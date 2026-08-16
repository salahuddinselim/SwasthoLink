<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP-based two-factor authentication (RFC 6238), restricted to Admin and
 * Hospital accounts — see User::canUseTwoFactor(). Secrets and recovery
 * codes are stored encrypted at rest via model casts (Crypt/APP_KEY), the
 * same pattern used for RSA private keys elsewhere in this app.
 */
class TwoFactorAuthenticationService
{
    private Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function qrCodeUri(User $user, string $secret): string
    {
        return $this->engine->getQRCodeUrl(
            config('app.name', 'SwasthoLink'),
            $user->email,
            $secret,
        );
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->engine->verifyKey($secret, str_replace(' ', '', $code));
    }

    /**
     * The current valid TOTP for a secret. Used by tests (which can't scan
     * a QR code) and nowhere in production request handling.
     */
    public function currentCode(string $secret): string
    {
        return $this->engine->getCurrentOtp($secret);
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $normalized = strtoupper(trim($code));

        if (! in_array($normalized, $codes, true)) {
            return false;
        }

        $user->update([
            'two_factor_recovery_codes' => array_values(array_diff($codes, [$normalized])),
        ]);

        return true;
    }
}
