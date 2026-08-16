<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Hospital;
use App\Models\HospitalShare;
use App\Models\Prescription;
use App\Services\DhKeyExchangeService;
use App\Services\EnvelopeEncryptionService;
use App\Services\NotificationService;
use App\Services\RsaKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use RuntimeException;

/**
 * Hospital-to-hospital prescription record sharing, secured with a
 * Diffie-Hellman key exchange whose derived secret seeds an AES-256-GCM
 * envelope: the payload is encrypted once with that key, which is then
 * RSA-wrapped separately for each hospital so nobody else — including the
 * server operator reading the database directly — can read the shared
 * record without one of the two hospitals' private RSA keys.
 */
class ShareController extends Controller
{
    public function __construct(
        private DhKeyExchangeService $dh,
        private EnvelopeEncryptionService $envelope,
        private RsaKeyService $rsaKeys,
        private NotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $hospital = $request->user()->hospital;

        $outgoing = HospitalShare::with(['recipientHospital', 'prescription'])
            ->where('initiator_hospital_id', $hospital->id)
            ->latest()
            ->get();

        $incoming = HospitalShare::with(['initiatorHospital', 'prescription'])
            ->where('recipient_hospital_id', $hospital->id)
            ->latest()
            ->get();

        $prescriptions = Prescription::where('hospital_id', $hospital->id)->latest()->limit(50)->get();

        $partnerHospitals = Hospital::whereNotNull('rsa_public_key')
            ->where('id', '!=', $hospital->id)
            ->orderBy('name')
            ->get();

        return view('hospital.shares.index', [
            'hospital' => $hospital,
            'outgoing' => $outgoing,
            'incoming' => $incoming,
            'prescriptions' => $prescriptions,
            'partnerHospitals' => $partnerHospitals,
        ]);
    }

    /**
     * Step 1: initiator hospital picks one of its own prescriptions and a
     * target hospital, generates its DH keypair, and publishes only the
     * public value. Its private exponent is held encrypted-at-rest until
     * the recipient accepts (see migration comment for why).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'prescription_id' => ['required', 'exists:prescriptions,id'],
            'recipient_hospital_id' => ['required', 'exists:hospitals,id'],
        ]);

        $hospital = $request->user()->hospital;

        $prescription = Prescription::where('hospital_id', $hospital->id)
            ->findOrFail($request->prescription_id);

        if ((int) $request->recipient_hospital_id === $hospital->id) {
            return back()->with('error', 'Choose a different hospital to share with.');
        }

        $recipient = Hospital::whereNotNull('rsa_public_key')->findOrFail($request->recipient_hospital_id);

        ['p' => $p, 'g' => $g] = $this->dh->primeAndGenerator();
        $a = $this->dh->generatePrivateExponent();
        $publicValue = $this->dh->publicValue($g, $a, $p);

        $share = HospitalShare::create([
            'initiator_hospital_id' => $hospital->id,
            'recipient_hospital_id' => $recipient->id,
            'prescription_id' => $prescription->id,
            'initiated_by' => $request->user()->id,
            'dh_prime' => $p,
            'dh_generator' => $g,
            'initiator_public_value' => $publicValue,
            'initiator_private_exponent_encrypted' => Crypt::encryptString($a),
            'status' => 'pending',
        ]);

        AuditLog::record('hospital_share.initiated', $share, ['recipient_hospital_id' => $recipient->id]);

        $this->notifications->notify(
            $recipient->user,
            'hospital_share.initiated',
            "{$hospital->name} wants to share a prescription record with you",
            $prescription->lookup_code,
            route('hospital.shares.index'),
        );

        return redirect()->route('hospital.shares.index')
            ->with('status', "Share request sent to {$recipient->name}. It completes once they accept.");
    }

    /**
     * Step 2: recipient hospital accepts. Generates its own DH keypair,
     * derives the shared secret from the initiator's public value, and uses
     * SHA-256(shared secret) as a one-time AES-256-GCM key to encrypt the
     * prescription. That raw key is then wrapped once per hospital with
     * RSA-OAEP so each side can independently unwrap it later. Both DH
     * private exponents are discarded the moment this completes.
     */
    public function accept(Request $request, HospitalShare $share): RedirectResponse
    {
        $hospital = $request->user()->hospital;

        if ($share->recipient_hospital_id !== $hospital->id || $share->status !== 'pending') {
            abort(404);
        }

        $b = $this->dh->generatePrivateExponent();
        $recipientPublicValue = $this->dh->publicValue($share->dh_generator, $b, $share->dh_prime);
        $sharedSecretViaB = $this->dh->sharedSecret($share->initiator_public_value, $b, $share->dh_prime);

        $a = Crypt::decryptString($share->initiator_private_exponent_encrypted);
        $sharedSecretViaA = $this->dh->sharedSecret($recipientPublicValue, $a, $share->dh_prime);

        if (! hash_equals($sharedSecretViaA, $sharedSecretViaB)) {
            // Would indicate a corrupted exchange; never expected to happen.
            throw new RuntimeException('DH shared secret mismatch during hospital share acceptance.');
        }

        $rawAesKey = $this->dh->deriveAesKey($sharedSecretViaA);

        $prescription = $share->prescription;
        $payload = json_encode([
            'lookup_code' => $prescription->lookup_code,
            'patient_name' => $prescription->patient_name,
            'patient_email' => $prescription->patient_email,
            'medicines' => $prescription->medicines,
            'notes' => $prescription->notes,
            'doctor_name' => $prescription->doctor->name,
            'doctor_signature' => $prescription->signature,
            'issued_at' => $prescription->created_at->toIso8601String(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $encrypted = $this->envelope->encrypt($payload, $rawAesKey);

        $share->update([
            'recipient_public_value' => $recipientPublicValue,
            'shared_secret_fingerprint' => hash('sha256', $sharedSecretViaA),
            'ciphertext' => $encrypted['ciphertext'],
            'iv' => $encrypted['iv'],
            'auth_tag' => $encrypted['tag'],
            'key_wrapped_for_initiator' => $this->envelope->wrapKey($rawAesKey, $share->initiatorHospital->rsa_public_key),
            'key_wrapped_for_recipient' => $this->envelope->wrapKey($rawAesKey, $hospital->rsa_public_key),
            'status' => 'completed',
            'accepted_by' => $request->user()->id,
            'accepted_at' => now(),
            // Forward secrecy: the ephemeral private exponent is no longer needed.
            'initiator_private_exponent_encrypted' => null,
        ]);

        AuditLog::record('hospital_share.accepted', $share);

        $this->notifications->notify(
            $share->initiatorHospital->user,
            'hospital_share.accepted',
            "{$hospital->name} accepted your share request",
            $prescription->lookup_code,
            route('hospital.shares.show', $share),
        );

        return redirect()->route('hospital.shares.index')->with('status', 'Share accepted and decrypted successfully — key exchange complete.');
    }

    /**
     * Either party to a completed share can revoke it — e.g. it was shared
     * with the wrong hospital, or the patient later objected. Revoking wipes
     * the encrypted payload and both wrapped keys so the record can no
     * longer be decrypted by anyone, even from a database backup; it does
     * not touch the original prescription, only this share.
     */
    public function revoke(Request $request, HospitalShare $share): RedirectResponse
    {
        $hospital = $request->user()->hospital;

        $isParty = in_array($hospital->id, [$share->initiator_hospital_id, $share->recipient_hospital_id], true);

        if (! $isParty || $share->status !== 'completed') {
            abort(404);
        }

        $share->update([
            'status' => 'revoked',
            'revoked_by' => $request->user()->id,
            'revoked_at' => now(),
            'ciphertext' => null,
            'iv' => null,
            'auth_tag' => null,
            'key_wrapped_for_initiator' => null,
            'key_wrapped_for_recipient' => null,
        ]);

        AuditLog::record('hospital_share.revoked', $share);

        $otherHospital = $hospital->id === $share->initiator_hospital_id ? $share->recipientHospital : $share->initiatorHospital;
        $this->notifications->notify(
            $otherHospital->user,
            'hospital_share.revoked',
            "{$hospital->name} revoked a shared record",
            null,
            route('hospital.shares.index'),
        );

        return redirect()->route('hospital.shares.index')->with('status', 'Share revoked — the record is no longer accessible to either hospital.');
    }

    public function reject(Request $request, HospitalShare $share): RedirectResponse
    {
        $hospital = $request->user()->hospital;

        if ($share->recipient_hospital_id !== $hospital->id || $share->status !== 'pending') {
            abort(404);
        }

        $share->update([
            'status' => 'rejected',
            'initiator_private_exponent_encrypted' => null,
        ]);

        AuditLog::record('hospital_share.rejected', $share);

        $this->notifications->notify(
            $share->initiatorHospital->user,
            'hospital_share.rejected',
            "{$hospital->name} rejected your share request",
            null,
            route('hospital.shares.index'),
        );

        return redirect()->route('hospital.shares.index')->with('status', 'Share request rejected.');
    }

    /**
     * Decrypt and display a completed share. Either hospital involved can
     * view it, each unwrapping the AES key with its own RSA private key.
     */
    public function show(Request $request, HospitalShare $share): View
    {
        $hospital = $request->user()->hospital;

        $isInitiator = $share->initiator_hospital_id === $hospital->id;
        $isRecipient = $share->recipient_hospital_id === $hospital->id;

        if (! $isInitiator && ! $isRecipient) {
            abort(404);
        }

        if ($share->status !== 'completed') {
            abort(404);
        }

        $wrappedKey = $isInitiator ? $share->key_wrapped_for_initiator : $share->key_wrapped_for_recipient;
        $privateKeyPem = $this->rsaKeys->decryptPrivateKey($hospital->rsa_private_key_encrypted);
        $rawAesKey = $this->envelope->unwrapKey($wrappedKey, $privateKeyPem);

        $payload = json_decode(
            $this->envelope->decrypt($share->ciphertext, $share->iv, $share->auth_tag, $rawAesKey),
            true,
        );

        AuditLog::record('hospital_share.viewed', $share);

        $otherHospital = $isInitiator ? $share->recipientHospital : $share->initiatorHospital;

        return view('hospital.shares.show', ['share' => $share, 'payload' => $payload, 'otherHospital' => $otherHospital]);
    }
}
