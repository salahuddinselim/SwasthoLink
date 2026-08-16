<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Second step of login for users with 2FA enabled. AuthenticatedSessionController
 * verifies the password, then — instead of establishing a real session —
 * stashes the user's id in session under `two_factor.user_id` and sends them
 * here. Only a valid TOTP code or recovery code completes the login.
 */
class TwoFactorChallengeController extends Controller
{
    public function __construct(private TwoFactorAuthenticationService $twoFactor) {}

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate(['code' => ['required', 'string']]);

        $user = User::findOrFail($userId);

        $valid = $this->twoFactor->verify($user->two_factor_secret, $request->code)
            || $this->twoFactor->consumeRecoveryCode($user, $request->code);

        if (! $valid) {
            AuditLog::record('two_factor.challenge_failed', $user);

            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        $request->session()->forget('two_factor.user_id');

        Auth::login($user, $request->session()->pull('two_factor.remember', false));
        $request->session()->regenerate();

        AuditLog::record('two_factor.challenge_passed', $user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
