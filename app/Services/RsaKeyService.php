<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use RuntimeException;

/**
 * Generates and unwraps the RSA-2048 keypairs used to sign prescriptions
 * (doctors) and to receive AES keys via envelope encryption (hospitals).
 *
 * Private keys are never stored in plaintext: they're encrypted at rest with
 * Laravel's application-key-backed encrypter (Crypt::encryptString, AES-256-CBC
 * under APP_KEY) and only decrypted in memory for the duration of a sign/unwrap
 * operation.
 */
class RsaKeyService
{
    /**
     * @return array{public_key: string, private_key_encrypted: string}
     */
    public function generateKeyPair(): array
    {
        $options = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // XAMPP on Windows ships its own openssl.cnf but PHP's compiled-in
        // default config path often points somewhere that doesn't exist on
        // this machine, which makes openssl_pkey_new() fail outright. Point
        // it at the config bundled next to the running PHP binary if present.
        if ($configPath = $this->findOpensslConfig()) {
            $options['config'] = $configPath;
        }

        $resource = openssl_pkey_new($options);

        if ($resource === false) {
            throw new RuntimeException('Failed to generate RSA keypair: '.openssl_error_string());
        }

        // openssl_pkey_export() resolves its own config independently of
        // openssl_pkey_new() and hits the same broken-default-path problem,
        // so it needs the same 'config' option passed again here.
        $exported = openssl_pkey_export($resource, $privateKeyPem, null, $options);

        if (! $exported) {
            throw new RuntimeException('Failed to export RSA private key: '.openssl_error_string());
        }

        $publicKeyPem = openssl_pkey_get_details($resource)['key'];

        return [
            'public_key' => $publicKeyPem,
            'private_key_encrypted' => Crypt::encryptString($privateKeyPem),
        ];
    }

    public function decryptPrivateKey(string $encryptedPrivateKey): string
    {
        return Crypt::decryptString($encryptedPrivateKey);
    }

    private function findOpensslConfig(): ?string
    {
        if ($env = getenv('OPENSSL_CONF')) {
            return $env;
        }

        $binDir = dirname(PHP_BINARY);

        foreach (['extras/openssl/openssl.cnf', 'extras/ssl/openssl.cnf'] as $relative) {
            $candidate = $binDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
