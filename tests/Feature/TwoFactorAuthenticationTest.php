<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_setup_page_generates_a_pending_secret(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->get(route('two-factor.show'))->assertOk();

        $this->assertNotNull(session('two_factor_setup_secret'));
    }

    public function test_doctor_and_patient_cannot_access_two_factor_setup(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor', 'status' => 'active']);
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($doctor)->get(route('two-factor.show'))->assertForbidden();
        $this->actingAs($patient)->get(route('two-factor.show'))->assertForbidden();
    }

    public function test_full_enable_login_challenge_and_disable_cycle(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'password' => 'Password123!']);
        $twoFactor = app(TwoFactorAuthenticationService::class);

        // Enable
        $this->actingAs($admin)->get(route('two-factor.show'));
        $secret = session('two_factor_setup_secret');
        $otp = $twoFactor->currentCode($secret);

        $this->actingAs($admin)->post(route('two-factor.confirm'), ['code' => $otp])
            ->assertRedirect(route('two-factor.show'));

        $admin->refresh();
        $this->assertTrue($admin->hasTwoFactorEnabled());
        $this->assertNotNull($admin->two_factor_secret);
        $this->assertCount(8, $admin->two_factor_recovery_codes);

        // Logging in with correct password now redirects to the 2FA challenge,
        // NOT straight to the dashboard.
        $this->post('/logout');

        $login = $this->post(route('login'), ['email' => $admin->email, 'password' => 'Password123!']);
        $login->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        // Wrong code fails and still doesn't log in.
        $this->post(route('two-factor.challenge'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        // Correct TOTP code completes login.
        $freshOtp = $twoFactor->currentCode($admin->two_factor_secret);
        $this->post(route('two-factor.challenge'), ['code' => $freshOtp])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);

        // A recovery code works too, and is single-use.
        $this->post('/logout');
        $recoveryCode = $admin->two_factor_recovery_codes[0];

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'Password123!']);
        $this->post(route('two-factor.challenge'), ['code' => $recoveryCode]);
        $this->assertAuthenticatedAs($admin);

        $this->post('/logout');
        $this->post(route('login'), ['email' => $admin->email, 'password' => 'Password123!']);
        $this->post(route('two-factor.challenge'), ['code' => $recoveryCode])->assertSessionHasErrors('code');
        $this->assertGuest();

        // Disable requires the current password.
        $freshOtp2 = $twoFactor->currentCode($admin->two_factor_secret);
        $this->post(route('login'), ['email' => $admin->email, 'password' => 'Password123!']);
        $this->post(route('two-factor.challenge'), ['code' => $freshOtp2]);

        $this->actingAs($admin)->delete(route('two-factor.disable'), ['password' => 'Password123!'])
            ->assertRedirect(route('two-factor.show'));

        $admin->refresh();
        $this->assertFalse($admin->hasTwoFactorEnabled());
        $this->assertNull($admin->two_factor_secret);

        // Login no longer requires a 2FA step.
        $this->post('/logout');
        $this->post(route('login'), ['email' => $admin->email, 'password' => 'Password123!'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_hospital_can_use_two_factor_too(): void
    {
        $user = User::factory()->create(['role' => 'hospital', 'status' => 'active']);
        Hospital::create([
            'user_id' => $user->id,
            'name' => 'Test Hospital',
            'registration_number' => 'REG-'.uniqid(),
            'verified_at' => now(),
        ]);

        $this->actingAs($user)->get(route('two-factor.show'))->assertOk();
    }
}
