<?php

namespace App\Services;

use App\Models\Prescription;
use RuntimeException;

/**
 * Signs and verifies prescriptions with RSA-SHA256 (PKCS#1 v1.5), so a
 * pharmacist can cryptographically confirm a prescription was actually
 * issued by the doctor it claims to be from and hasn't been tampered with
 * since (forged medicines, altered patient, etc.).
 */
class PrescriptionSigningService
{
    public function __construct(private RsaKeyService $rsaKeys) {}

    /**
     * Canonical byte representation that gets signed/verified. Every field
     * that a forger could plausibly want to alter is included, keyed and
     * delimited unambiguously so no field-concatenation attack is possible.
     */
    public function canonicalPayload(
        string $lookupCode,
        int $doctorId,
        ?int $hospitalId,
        string $patientName,
        ?string $patientEmail,
        string $medicines,
        ?string $notes,
    ): string {
        return json_encode([
            'lookup_code' => $lookupCode,
            'doctor_id' => $doctorId,
            'hospital_id' => $hospitalId,
            'patient_name' => $patientName,
            'patient_email' => $patientEmail,
            'medicines' => $medicines,
            'notes' => $notes,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function sign(Prescription $prescription, string $encryptedPrivateKey): string
    {
        $privateKeyPem = $this->rsaKeys->decryptPrivateKey($encryptedPrivateKey);
        $privateKey = openssl_pkey_get_private($privateKeyPem);

        if ($privateKey === false) {
            throw new RuntimeException('Invalid RSA private key while signing prescription.');
        }

        $payload = $this->canonicalPayload(
            $prescription->lookup_code,
            $prescription->doctor_id,
            $prescription->hospital_id,
            $prescription->patient_name,
            $prescription->patient_email,
            $prescription->medicines,
            $prescription->notes,
        );

        openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    public function verify(Prescription $prescription, string $publicKeyPem): bool
    {
        if (! $prescription->signature) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($publicKeyPem);

        if ($publicKey === false) {
            return false;
        }

        $payload = $this->canonicalPayload(
            $prescription->lookup_code,
            $prescription->doctor_id,
            $prescription->hospital_id,
            $prescription->patient_name,
            $prescription->patient_email,
            $prescription->medicines,
            $prescription->notes,
        );

        $signature = base64_decode($prescription->signature, true);

        if ($signature === false) {
            return false;
        }

        return openssl_verify($payload, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
}
