<?php

namespace App\Services;

use RuntimeException;

/**
 * AES-256-GCM "envelope encryption": a payload is encrypted once with a raw
 * data key, and that raw key is then wrapped (RSA-OAEP encrypted) separately
 * for each recipient so they can each unwrap it with their own private key
 * without the raw key ever touching storage.
 */
class EnvelopeEncryptionService
{
    /**
     * @return array{ciphertext: string, iv: string, tag: string}
     */
    public function encrypt(string $plaintext, string $rawKey): array
    {
        $iv = random_bytes(12); // 96-bit nonce, standard for GCM

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $rawKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
        );

        if ($ciphertext === false) {
            throw new RuntimeException('AES-256-GCM encryption failed: '.openssl_error_string());
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
        ];
    }

    public function decrypt(string $ciphertextB64, string $ivB64, string $tagB64, string $rawKey): string
    {
        $plaintext = openssl_decrypt(
            base64_decode($ciphertextB64),
            'aes-256-gcm',
            $rawKey,
            OPENSSL_RAW_DATA,
            base64_decode($ivB64),
            base64_decode($tagB64),
        );

        if ($plaintext === false) {
            throw new RuntimeException('AES-256-GCM decryption failed — ciphertext or auth tag invalid.');
        }

        return $plaintext;
    }

    /** RSA-OAEP wrap a raw AES key with a recipient's public key. */
    public function wrapKey(string $rawKey, string $publicKeyPem): string
    {
        $publicKey = openssl_pkey_get_public($publicKeyPem);

        if ($publicKey === false) {
            throw new RuntimeException('Invalid RSA public key for key wrapping.');
        }

        if (! openssl_public_encrypt($rawKey, $wrapped, $publicKey, OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new RuntimeException('RSA key wrapping failed: '.openssl_error_string());
        }

        return base64_encode($wrapped);
    }

    /** RSA-OAEP unwrap a raw AES key with the recipient's private key. */
    public function unwrapKey(string $wrappedKeyB64, string $privateKeyPem): string
    {
        $privateKey = openssl_pkey_get_private($privateKeyPem);

        if ($privateKey === false) {
            throw new RuntimeException('Invalid RSA private key for key unwrapping.');
        }

        if (! openssl_private_decrypt(base64_decode($wrappedKeyB64), $rawKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new RuntimeException('RSA key unwrapping failed: '.openssl_error_string());
        }

        return $rawKey;
    }
}
