<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\HospitalShare;
use App\Models\Prescription;
use App\Models\User;
use App\Services\RsaKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalShareTest extends TestCase
{
    use RefreshDatabase;

    private function makeApprovedHospital(string $name): array
    {
        $keys = app(RsaKeyService::class)->generateKeyPair();

        $user = User::factory()->create(['role' => 'hospital', 'status' => 'active']);
        $hospital = Hospital::create([
            'user_id' => $user->id,
            'name' => $name,
            'registration_number' => 'REG-'.uniqid(),
            'rsa_public_key' => $keys['public_key'],
            'rsa_private_key_encrypted' => $keys['private_key_encrypted'],
            'verified_at' => now(),
        ]);

        return [$user, $hospital];
    }

    public function test_hospital_to_hospital_dh_exchange_encrypts_and_both_sides_can_decrypt(): void
    {
        [$userA, $hospitalA] = $this->makeApprovedHospital('Hospital A');
        [$userB, $hospitalB] = $this->makeApprovedHospital('Hospital B');

        $doctorKeys = app(RsaKeyService::class)->generateKeyPair();
        $doctorUser = User::factory()->create(['role' => 'doctor', 'status' => 'active']);
        DoctorProfile::create([
            'user_id' => $doctorUser->id,
            'hospital_id' => $hospitalA->id,
            'bmdc_number' => 'BMDC-'.uniqid(),
            'rsa_public_key' => $doctorKeys['public_key'],
            'rsa_private_key_encrypted' => $doctorKeys['private_key_encrypted'],
            'verified_at' => now(),
        ]);

        $prescription = Prescription::create([
            'doctor_id' => $doctorUser->id,
            'hospital_id' => $hospitalA->id,
            'patient_name' => 'John Smith',
            'patient_phone' => '01899998888',
            'medicines' => 'Amoxicillin 500mg',
            'notes' => 'Take with food',
        ]);

        // Step 1: Hospital A initiates.
        $initiate = $this->actingAs($userA)->post(route('hospital.shares.store'), [
            'prescription_id' => $prescription->id,
            'recipient_hospital_id' => $hospitalB->id,
        ]);
        $initiate->assertRedirect(route('hospital.shares.index'));

        $share = HospitalShare::firstOrFail();
        $this->assertEquals('pending', $share->status);
        $this->assertNotNull($share->initiator_private_exponent_encrypted);
        $this->assertNull($share->ciphertext);

        // Step 2: Hospital B accepts, completing the DH exchange and AES encryption.
        $accept = $this->actingAs($userB)->post(route('hospital.shares.accept', $share));
        $accept->assertRedirect(route('hospital.shares.index'));

        $share->refresh();
        $this->assertEquals('completed', $share->status);
        $this->assertNotNull($share->ciphertext);
        $this->assertNotNull($share->shared_secret_fingerprint);
        // Forward secrecy: the ephemeral private exponent must be wiped after use.
        $this->assertNull($share->initiator_private_exponent_encrypted);

        // Both hospitals can independently decrypt via their own RSA private key.
        $viewAsInitiator = $this->actingAs($userA)->get(route('hospital.shares.show', $share));
        $viewAsInitiator->assertOk();
        $viewAsInitiator->assertSee('Amoxicillin 500mg');

        $viewAsRecipient = $this->actingAs($userB)->get(route('hospital.shares.show', $share));
        $viewAsRecipient->assertOk();
        $viewAsRecipient->assertSee('Amoxicillin 500mg');

        // A third, uninvolved hospital must not be able to view the share.
        [$userC] = $this->makeApprovedHospital('Hospital C');
        $this->actingAs($userC)->get(route('hospital.shares.show', $share))->assertNotFound();
    }

    public function test_recipient_can_reject_a_pending_share(): void
    {
        [$userA, $hospitalA] = $this->makeApprovedHospital('Hospital A');
        [$userB, $hospitalB] = $this->makeApprovedHospital('Hospital B');

        $doctorKeys = app(RsaKeyService::class)->generateKeyPair();
        $doctorUser = User::factory()->create(['role' => 'doctor', 'status' => 'active']);
        DoctorProfile::create([
            'user_id' => $doctorUser->id,
            'hospital_id' => $hospitalA->id,
            'bmdc_number' => 'BMDC-'.uniqid(),
            'rsa_public_key' => $doctorKeys['public_key'],
            'rsa_private_key_encrypted' => $doctorKeys['private_key_encrypted'],
            'verified_at' => now(),
        ]);

        $prescription = Prescription::create([
            'doctor_id' => $doctorUser->id,
            'hospital_id' => $hospitalA->id,
            'patient_name' => 'John Smith',
            'patient_phone' => '01899998888',
            'medicines' => 'Amoxicillin 500mg',
        ]);

        $this->actingAs($userA)->post(route('hospital.shares.store'), [
            'prescription_id' => $prescription->id,
            'recipient_hospital_id' => $hospitalB->id,
        ]);

        $share = HospitalShare::firstOrFail();

        $this->actingAs($userB)->post(route('hospital.shares.reject', $share))
            ->assertRedirect(route('hospital.shares.index'));

        $share->refresh();
        $this->assertEquals('rejected', $share->status);
        $this->assertNull($share->initiator_private_exponent_encrypted);
    }
}
