<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\PatientAccessGrant;
use App\Models\Prescription;
use App\Models\User;
use App\Services\RsaKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRecordAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeDoctor(string $hospitalName): array
    {
        $rsaKeys = app(RsaKeyService::class);

        $hospitalUser = User::factory()->create(['role' => 'hospital', 'status' => 'active']);
        $hospital = Hospital::create([
            'user_id' => $hospitalUser->id,
            'name' => $hospitalName,
            'registration_number' => 'REG-'.uniqid(),
            'verified_at' => now(),
        ]);

        $doctorKeys = $rsaKeys->generateKeyPair();
        $doctorUser = User::factory()->create(['role' => 'doctor', 'status' => 'active']);
        DoctorProfile::create([
            'user_id' => $doctorUser->id,
            'hospital_id' => $hospital->id,
            'bmdc_number' => 'BMDC-'.uniqid(),
            'rsa_public_key' => $doctorKeys['public_key'],
            'rsa_private_key_encrypted' => $doctorKeys['private_key_encrypted'],
            'verified_at' => now(),
        ]);

        return [$doctorUser, $hospital];
    }

    public function test_doctor_automatically_sees_same_hospital_history_without_a_code(): void
    {
        [$doctorUser, $hospital] = $this->makeDoctor('Dhaka Central');
        $patient = User::factory()->create(['role' => 'patient', 'email' => 'karim@example.test']);

        Prescription::create([
            'doctor_id' => $doctorUser->id,
            'hospital_id' => $hospital->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Karim Hasan',
            'patient_email' => $patient->email,
            'patient_phone' => '01700000000',
            'medicines' => 'Seclo 20mg',
        ]);

        $response = $this->actingAs($doctorUser)->post(route('doctor.patients.search'), [
            'patient_identifier' => $patient->email,
        ]);

        $response->assertOk();
        $response->assertSee('Seclo 20mg');
    }

    public function test_doctor_cannot_see_other_hospital_history_without_a_valid_access_code(): void
    {
        [$doctorA, $hospitalA] = $this->makeDoctor('Dhaka Central');
        [$doctorB, $hospitalB] = $this->makeDoctor('Chittagong General');
        $patient = User::factory()->create(['role' => 'patient', 'email' => 'karim@example.test']);

        Prescription::create([
            'doctor_id' => $doctorB->id,
            'hospital_id' => $hospitalB->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Karim Hasan',
            'patient_email' => $patient->email,
            'patient_phone' => '01700000000',
            'medicines' => 'Atorvastatin 20mg',
        ]);

        // Doctor A (different hospital) searches without a code.
        $response = $this->actingAs($doctorA)->post(route('doctor.patients.search'), [
            'patient_identifier' => $patient->email,
        ]);

        $response->assertOk();
        $response->assertDontSee('Atorvastatin 20mg');
    }

    public function test_valid_access_code_unlocks_other_hospital_history_and_is_audited(): void
    {
        [$doctorA, $hospitalA] = $this->makeDoctor('Dhaka Central');
        [$doctorB, $hospitalB] = $this->makeDoctor('Chittagong General');
        $patient = User::factory()->create(['role' => 'patient', 'email' => 'karim@example.test']);

        Prescription::create([
            'doctor_id' => $doctorB->id,
            'hospital_id' => $hospitalB->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Karim Hasan',
            'patient_email' => $patient->email,
            'patient_phone' => '01700000000',
            'medicines' => 'Atorvastatin 20mg',
        ]);

        // Patient generates an access code.
        $this->actingAs($patient)->post(route('patient.access.store'))
            ->assertRedirect(route('patient.access.index'));

        $grant = PatientAccessGrant::firstOrFail();
        $this->assertEquals(0, $grant->use_count);

        $response = $this->actingAs($doctorA)->post(route('doctor.patients.search'), [
            'patient_identifier' => $patient->email,
            'access_code' => $grant->code,
        ]);

        $response->assertOk();
        $response->assertSee('Atorvastatin 20mg');

        $grant->refresh();
        $this->assertEquals(1, $grant->use_count);
        $this->assertNotNull($grant->last_used_at);
    }

    public function test_revoked_access_code_is_rejected(): void
    {
        [$doctorA, $hospitalA] = $this->makeDoctor('Dhaka Central');
        [$doctorB, $hospitalB] = $this->makeDoctor('Chittagong General');
        $patient = User::factory()->create(['role' => 'patient', 'email' => 'karim@example.test']);

        Prescription::create([
            'doctor_id' => $doctorB->id,
            'hospital_id' => $hospitalB->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Karim Hasan',
            'patient_email' => $patient->email,
            'patient_phone' => '01700000000',
            'medicines' => 'Atorvastatin 20mg',
        ]);

        $this->actingAs($patient)->post(route('patient.access.store'));
        $grant = PatientAccessGrant::firstOrFail();

        $this->actingAs($patient)->post(route('patient.access.revoke', $grant))
            ->assertRedirect(route('patient.access.index'));

        $response = $this->actingAs($doctorA)->post(route('doctor.patients.search'), [
            'patient_identifier' => $patient->email,
            'access_code' => $grant->code,
        ]);

        $response->assertOk();
        $response->assertDontSee('Atorvastatin 20mg');
        $response->assertSee('invalid, expired, or has been revoked');
    }

    public function test_expired_access_code_is_rejected(): void
    {
        [$doctorA, $hospitalA] = $this->makeDoctor('Dhaka Central');
        $patient = User::factory()->create(['role' => 'patient', 'email' => 'karim@example.test']);

        $grant = PatientAccessGrant::create([
            'patient_id' => $patient->id,
            'code' => PatientAccessGrant::generateUniqueCode(),
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($doctorA)->post(route('doctor.patients.search'), [
            'patient_identifier' => $patient->email,
            'access_code' => $grant->code,
        ]);

        $response->assertOk();
        $response->assertSee('invalid, expired, or has been revoked');
    }

    public function test_patient_cannot_revoke_another_patients_access_grant(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $otherPatient = User::factory()->create(['role' => 'patient']);

        $grant = PatientAccessGrant::create([
            'patient_id' => $patient->id,
            'code' => PatientAccessGrant::generateUniqueCode(),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($otherPatient)->post(route('patient.access.revoke', $grant))->assertNotFound();

        $grant->refresh();
        $this->assertNull($grant->revoked_at);
    }
}
