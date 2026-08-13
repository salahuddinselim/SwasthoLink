<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Prescription;
use App\Models\User;
use App\Services\PrescriptionSigningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function __construct(private PrescriptionSigningService $signing) {}

    /**
     * Doctor: form to write a new prescription.
     */
    public function create(): View
    {
        return view('doctor.prescriptions.create');
    }

    /**
     * Doctor: store a new prescription, signed with the doctor's RSA private
     * key. Patients are matched by email if they already have an account;
     * otherwise the prescription is still created and can be claimed by
     * lookup code at the pharmacy.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_email' => ['nullable', 'email', 'max:255'],
            'patient_phone' => ['required', 'string', 'max:32', 'min:4'],
            'medicines' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $doctor = $request->user();
        $doctorProfile = $doctor->doctorProfile;

        if (! $doctorProfile?->rsa_private_key_encrypted) {
            return back()->withInput()->with('error',
                'Your account does not have a signing key yet. Contact an administrator.');
        }

        $patient = $request->patient_email
            ? User::where('email', $request->patient_email)->where('role', 'patient')->first()
            : null;

        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'hospital_id' => $doctorProfile->hospital_id,
            'patient_id' => $patient?->id,
            'patient_name' => $request->patient_name,
            'patient_email' => $request->patient_email,
            'patient_phone' => $request->patient_phone,
            'medicines' => $request->medicines,
            'notes' => $request->notes,
        ]);

        $prescription->update([
            'signature' => $this->signing->sign($prescription, $doctorProfile->rsa_private_key_encrypted),
        ]);

        AuditLog::record('prescription.created', $prescription);

        return redirect()->route('doctor.prescriptions.index')
            ->with('status', "Prescription created. Lookup code: {$prescription->lookup_code}");
    }

    /**
     * Doctor: list prescriptions they've written.
     */
    public function doctorIndex(Request $request): View
    {
        $prescriptions = $request->user()->prescriptionsWritten()->latest()->paginate(15);

        return view('doctor.prescriptions.index', ['prescriptions' => $prescriptions]);
    }

    /**
     * Patient: list prescriptions matched to their account.
     */
    public function patientIndex(Request $request): View
    {
        $prescriptions = $request->user()->prescriptionsReceived()->with('doctor')->latest()->paginate(15);

        return view('patient.prescriptions.index', ['prescriptions' => $prescriptions]);
    }

    /**
     * Pharmacist: lookup form.
     */
    public function lookupForm(): View
    {
        return view('pharmacist.lookup');
    }

    /**
     * Pharmacist: stage 1 — resolve a lookup code. If found, valid, and not
     * expired, this does NOT reveal medicines/notes yet; it asks for the
     * patient's phone last 4 digits as a second factor before revealing
     * anything sensitive.
     */
    public function lookup(Request $request): View
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);

        $prescription = Prescription::with(['doctor.doctorProfile', 'hospital'])
            ->where('lookup_code', strtoupper(trim($request->code)))
            ->first();

        AuditLog::record('prescription.lookup', $prescription, ['code' => $request->code]);

        $expired = $prescription?->isExpired() ?? false;

        return view('pharmacist.lookup', [
            'prescription' => $expired ? null : $prescription,
            'expired' => $expired,
            'searched' => true,
            'awaitingSecondFactor' => $prescription && ! $expired,
        ]);
    }

    /**
     * Pharmacist: stage 2 — confirm the patient's phone last 4 digits, then
     * reveal the full prescription along with RSA signature verification
     * against the issuing doctor's public key.
     */
    public function verify(Request $request): View
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'phone_last4' => ['required', 'string', 'max:4'],
        ]);

        $prescription = Prescription::with(['doctor.doctorProfile', 'hospital'])
            ->where('lookup_code', strtoupper(trim($request->code)))
            ->first();

        if (! $prescription || $prescription->isExpired()) {
            AuditLog::record('prescription.verify_failed', $prescription, ['reason' => 'not_found_or_expired']);

            return view('pharmacist.lookup', ['prescription' => null, 'searched' => true]);
        }

        $expectedLast4 = substr(preg_replace('/\D/', '', (string) $prescription->patient_phone), -4);
        $givenLast4 = preg_replace('/\D/', '', $request->phone_last4);

        if ($expectedLast4 === '' || $givenLast4 !== $expectedLast4) {
            AuditLog::record('prescription.verify_failed', $prescription, ['reason' => 'phone_mismatch']);

            return view('pharmacist.lookup', [
                'prescription' => $prescription,
                'searched' => true,
                'awaitingSecondFactor' => true,
                'secondFactorError' => 'Phone digits did not match our records.',
            ]);
        }

        $signatureValid = $prescription->doctor->doctorProfile?->rsa_public_key
            && $this->signing->verify($prescription, $prescription->doctor->doctorProfile->rsa_public_key);

        AuditLog::record('prescription.verified', $prescription, ['signature_valid' => (bool) $signatureValid]);

        return view('pharmacist.lookup', [
            'prescription' => $prescription,
            'searched' => true,
            'revealed' => true,
            'signatureValid' => $signatureValid,
        ]);
    }

    /**
     * Pharmacist: mark a prescription as dispensed so its code can't be reused.
     */
    public function dispense(Request $request, Prescription $prescription): RedirectResponse
    {
        if ($prescription->status === 'dispensed') {
            return back()->with('error', 'This prescription has already been dispensed.');
        }

        if ($prescription->isExpired()) {
            return back()->with('error', 'This prescription code has expired and cannot be dispensed.');
        }

        $prescription->update([
            'status' => 'dispensed',
            'dispensed_by' => $request->user()->id,
            'dispensed_at' => now(),
        ]);

        AuditLog::record('prescription.dispensed', $prescription);

        return redirect()->route('pharmacist.lookup')
            ->with('status', "Prescription {$prescription->lookup_code} marked as dispensed.");
    }
}
