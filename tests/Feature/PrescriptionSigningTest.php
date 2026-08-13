<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\Prescription;
use App\Models\User;
use App\Services\PrescriptionSigningService;
use App\Services\RsaKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionSigningTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveDoctor(): User
    {
        $keys = app(RsaKeyService::class)->generateKeyPair();

        $hospitalUser = User::factory()->create(['role' => 'hospital', 'status' => 'active']);
        $hospital = Hospital::create([
            'user_id' => $hospitalUser->id,
            'name' => 'City Hospital',
            'registration_number' => 'REG-'.uniqid(),
            'rsa_public_key' => $keys['public_key'],
            'rsa_private_key_encrypted' => $keys['private_key_encrypted'],
            'verified_at' => now(),
        ]);

        $doctorKeys = app(RsaKeyService::class)->generateKeyPair();
        $doctorUser = User::factory()->create(['role' => 'doctor', 'status' => 'active']);
        DoctorProfile::create([
            'user_id' => $doctorUser->id,
            'hospital_id' => $hospital->id,
            'bmdc_number' => 'BMDC-'.uniqid(),
            'rsa_public_key' => $doctorKeys['public_key'],
            'rsa_private_key_encrypted' => $doctorKeys['private_key_encrypted'],
            'verified_at' => now(),
        ]);

        return $doctorUser;
    }

    public function test_prescription_is_signed_on_creation_and_signature_verifies(): void
    {
        $doctor = $this->makeActiveDoctor();

        $response = $this->actingAs($doctor)->post(route('doctor.prescriptions.store'), [
            'patient_name' => 'Jane Doe',
            'patient_phone' => '01712345678',
            'medicines' => 'Napa Extra 500mg',
        ]);

        $response->assertRedirect(route('doctor.prescriptions.index'));

        $prescription = Prescription::firstOrFail();

        $this->assertNotNull($prescription->signature);

        $valid = app(PrescriptionSigningService::class)->verify(
            $prescription,
            $prescription->doctor->doctorProfile->rsa_public_key,
        );

        $this->assertTrue($valid);
    }

    public function test_signature_verification_fails_if_prescription_is_tampered(): void
    {
        $doctor = $this->makeActiveDoctor();

        $this->actingAs($doctor)->post(route('doctor.prescriptions.store'), [
            'patient_name' => 'Jane Doe',
            'patient_phone' => '01712345678',
            'medicines' => 'Napa Extra 500mg',
        ]);

        $prescription = Prescription::firstOrFail();
        $prescription->medicines = 'Something else entirely 999mg';

        $valid = app(PrescriptionSigningService::class)->verify(
            $prescription,
            $prescription->doctor->doctorProfile->rsa_public_key,
        );

        $this->assertFalse($valid);
    }

    public function test_pharmacist_lookup_requires_correct_phone_last_four_before_revealing_medicines(): void
    {
        $doctor = $this->makeActiveDoctor();

        $this->actingAs($doctor)->post(route('doctor.prescriptions.store'), [
            'patient_name' => 'Jane Doe',
            'patient_phone' => '01712345678',
            'medicines' => 'Napa Extra 500mg',
        ]);

        $prescription = Prescription::firstOrFail();

        $pharmacist = User::factory()->create(['role' => 'pharmacist', 'status' => 'active']);

        $firstStage = $this->actingAs($pharmacist)->post(route('pharmacist.lookup.search'), [
            'code' => $prescription->lookup_code,
        ]);
        $firstStage->assertDontSee('Napa Extra 500mg');

        $wrongFactor = $this->actingAs($pharmacist)->post(route('pharmacist.lookup.verify'), [
            'code' => $prescription->lookup_code,
            'phone_last4' => '0000',
        ]);
        $wrongFactor->assertDontSee('Napa Extra 500mg');

        $correctFactor = $this->actingAs($pharmacist)->post(route('pharmacist.lookup.verify'), [
            'code' => $prescription->lookup_code,
            'phone_last4' => '5678',
        ]);
        $correctFactor->assertSee('Napa Extra 500mg');
        $correctFactor->assertSee('Doctor signature verified');
    }

    public function test_expired_lookup_code_is_rejected(): void
    {
        $doctor = $this->makeActiveDoctor();

        $this->actingAs($doctor)->post(route('doctor.prescriptions.store'), [
            'patient_name' => 'Jane Doe',
            'patient_phone' => '01712345678',
            'medicines' => 'Napa Extra 500mg',
        ]);

        $prescription = Prescription::firstOrFail();
        $prescription->update(['expires_at' => now()->subDay()]);

        $pharmacist = User::factory()->create(['role' => 'pharmacist', 'status' => 'active']);

        $response = $this->actingAs($pharmacist)->post(route('pharmacist.lookup.search'), [
            'code' => $prescription->lookup_code,
        ]);

        $response->assertSee('expired');
    }
}
