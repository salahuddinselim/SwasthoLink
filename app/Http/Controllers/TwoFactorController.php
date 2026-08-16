<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Enrollment/management of TOTP 2FA (setup, confirm, disable) for the
 * currently authenticated Admin/Hospital user. The login-time challenge
 * lives in TwoFactorChallengeController — this controller only runs for
 * an already-authenticated session (e.g. from the Profile page).
 */
class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorAuthenticationService $twoFactor) {}

    public function show(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->canUseTwoFactor(), 403);

        if (! $user->hasTwoFactorEnabled() && ! $request->session()->has('two_factor_setup_secret')) {
            $secret = $this->twoFactor->generateSecret();
            $request->session()->put('two_factor_setup_secret', $secret);
        }

        $secret = $user->hasTwoFactorEnabled() ? null : $request->session()->get('two_factor_setup_secret');

        return view('two-factor.show', [
            'user' => $user,
            'secret' => $secret,
            'qrUri' => $secret ? $this->twoFactor->qrCodeUri($user, $secret) : null,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canUseTwoFactor(), 403);

        $request->validate(['code' => ['required', 'string']]);

        $secret = $request->session()->get('two_factor_setup_secret');

        if (! $secret || ! $this->twoFactor->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'That code did not match. Check your authenticator app and try again.']);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->forget('two_factor_setup_secret');

        AuditLog::record('two_factor.enabled', $user);

        return redirect()->route('two-factor.show')
            ->with('status', 'Two-factor authentication is now enabled.')
            ->with('two_factor_fresh_recovery_codes', $recoveryCodes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate(['password' => ['required', 'current_password']]);

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        AuditLog::record('two_factor.disabled', $user);

        return redirect()->route('two-factor.show')->with('status', 'Two-factor authentication has been disabled.');
    }
}
