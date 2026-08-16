<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\PharmacistProfile;
use App\Models\User;
use App\Services\RsaKeyService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with one demo account per role,
     * all pre-approved and active so every screen can be exercised
     * without manually running through the approval workflow.
     */
    public function run(): void
    {
        $rsaKeys = app(RsaKeyService::class);
        $demoPassword = 'Demo@1234';

        $admin = User::firstOrCreate(
            ['email' => 'admin@swastholink.test'],
            [
                'name' => 'SwasthoLink Admin',
                'password' => 'ChangeMe123!',
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $hospitalUser = User::firstOrCreate(
            ['email' => 'hospital@swastholink.test'],
            [
                'name' => 'Dhaka Central Hospital',
                'password' => $demoPassword,
                'role' => 'hospital',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $hospital = Hospital::firstOrCreate(
            ['user_id' => $hospitalUser->id],
            [
                'name' => 'Dhaka Central Hospital',
                'registration_number' => 'DGHS-DEMO-0001',
                'address' => 'Dhaka, Bangladesh',
            ]
        );

        if (! $hospital->rsa_public_key) {
            $keys = $rsaKeys->generateKeyPair();
            $hospital->update([
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rsa_public_key' => $keys['public_key'],
                'rsa_private_key_encrypted' => $keys['private_key_encrypted'],
            ]);
        }

        $hospitalTwoUser = User::firstOrCreate(
            ['email' => 'hospital2@swastholink.test'],
            [
                'name' => 'Chittagong General Hospital',
                'password' => $demoPassword,
                'role' => 'hospital',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $hospitalTwo = Hospital::firstOrCreate(
            ['user_id' => $hospitalTwoUser->id],
            [
                'name' => 'Chittagong General Hospital',
                'registration_number' => 'DGHS-DEMO-0002',
                'address' => 'Chittagong, Bangladesh',
            ]
        );

        if (! $hospitalTwo->rsa_public_key) {
            $keys = $rsaKeys->generateKeyPair();
            $hospitalTwo->update([
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rsa_public_key' => $keys['public_key'],
                'rsa_private_key_encrypted' => $keys['private_key_encrypted'],
            ]);
        }

        $doctorUser = User::firstOrCreate(
            ['email' => 'doctor@swastholink.test'],
            [
                'name' => 'Dr. Farhana Rahman',
                'password' => $demoPassword,
                'role' => 'doctor',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $doctorProfile = DoctorProfile::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'hospital_id' => $hospital->id,
                'bmdc_number' => 'BMDC-DEMO-0001',
                'specialization' => 'General Medicine',
            ]
        );

        if (! $doctorProfile->rsa_public_key) {
            $keys = $rsaKeys->generateKeyPair();
            $doctorProfile->update([
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rsa_public_key' => $keys['public_key'],
                'rsa_private_key_encrypted' => $keys['private_key_encrypted'],
            ]);
        }

        $doctorTwoUser = User::firstOrCreate(
            ['email' => 'doctor2@swastholink.test'],
            [
                'name' => 'Dr. Imran Kabir',
                'password' => $demoPassword,
                'role' => 'doctor',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $doctorTwoProfile = DoctorProfile::firstOrCreate(
            ['user_id' => $doctorTwoUser->id],
            [
                'hospital_id' => $hospitalTwo->id,
                'bmdc_number' => 'BMDC-DEMO-0002',
                'specialization' => 'Cardiology',
            ]
        );

        if (! $doctorTwoProfile->rsa_public_key) {
            $keys = $rsaKeys->generateKeyPair();
            $doctorTwoProfile->update([
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rsa_public_key' => $keys['public_key'],
                'rsa_private_key_encrypted' => $keys['private_key_encrypted'],
            ]);
        }

        $patientUser = User::firstOrCreate(
            ['email' => 'patient@swastholink.test'],
            [
                'name' => 'Karim Hasan',
                'password' => $demoPassword,
                'role' => 'patient',
                'status' => 'active',
                'phone' => '01700000000',
                'email_verified_at' => now(),
            ]
        );

        $pharmacistUser = User::firstOrCreate(
            ['email' => 'pharmacist@swastholink.test'],
            [
                'name' => 'Rafiq Islam',
                'password' => $demoPassword,
                'role' => 'pharmacist',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $pharmacistProfile = PharmacistProfile::firstOrCreate(
            ['user_id' => $pharmacistUser->id],
            [
                'pharmacy_name' => 'City Pharmacy',
                'pharmacy_license_number' => 'PHRM-DEMO-0001',
                'address' => 'Dhaka, Bangladesh',
            ]
        );

        if (! $pharmacistProfile->verified_at) {
            $pharmacistProfile->update([
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);
        }
    }
}
