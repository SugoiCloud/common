<?php

namespace SugoiCloud\Common;

use Random\RandomException;
use SugoiCloud\Common\Exceptions\CryptoException;

/**
 * Cryptographic utilities.
 */
class Crypto
{
    protected const CIPHER_ALGO = 'aes-256-cbc';

    /**
     * Generate a secure hash for the provided input.
     *
     * @param string $input
     * @return string
     */
    public static function hash(string $input): string
    {
        [$alg, $options] = self::getHashOptions();

        return password_hash($input, $alg, $options);
    }

    /**
     * Verifies that an input matches a given hash
     *
     * @param string $input
     * @param string $hash
     * @return bool
     */
    public static function matches(string $input, string $hash): bool
    {
        return password_verify($input, $hash);
    }

    /**
     * Check if the provided hash has outdated hashing options and should be re-hashed.
     *
     * @param string $hash
     * @return bool
     */
    public static function outdated(string $hash): bool
    {
        [$alg, $options] = self::getHashOptions();
        return password_needs_rehash($hash, $alg, $options);
    }

    /**
     * Generates a secure random string.
     *
     * @param int $length Length of the random string
     * @return string
     * @throws CryptoException
     */
    public static function random(int $length = 16): string
    {
        return bin2hex(self::generate($length / 2));
    }

    /**
     * Generates a secure random key suitable for encryption.
     *
     * @param int $bytes How many bytes to generate for the random data
     * @return string
     * @throws CryptoException if the random source is unavailable or fails.
     */
    public static function generate(int $bytes = 32): string
    {
        try {
            return random_bytes($bytes);
        } catch (RandomException $ex) {
            throw new CryptoException($ex->getMessage());
        }
    }

    /**
     * Encrypts data into a base64 encoded string using a symmetric key encryption (AES-256-CBC)
     *
     * @param string $data
     * @param string $key
     * @return string
     * @throws CryptoException if the encryption fails.
     */
    public static function encrypt(string $data, string $key): string
    {
        try {
            if (!extension_loaded('openssl')) {
                throw new CryptoException('openssl extension not loaded');
            }

            $iv = random_bytes(
                openssl_cipher_iv_length(self::CIPHER_ALGO)
                    ?: throw new CryptoException('Invalid cipher algo: ' . openssl_error_string())
            );

            $encrypted = openssl_encrypt($data, self::CIPHER_ALGO, $key, 0, $iv)
                ?: throw new CryptoException('Encryption failed: ' . openssl_error_string());

            return base64_encode($iv . $encrypted);
        } catch (RandomException $ex) {
            throw new CryptoException("Encryption failed: {$ex->getMessage()}");
        }
    }

    /**
     * Decrypts base64 decoded data encrypted with the encrypt method.
     *
     * @param string $data
     * @param string $key
     * @return string
     * @throws CryptoException
     */
    public static function decrypt(string $data, string $key): string
    {
        if (!extension_loaded('openssl')) {
            throw new CryptoException('openssl extension not loaded');
        }

        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length(self::CIPHER_ALGO)
            ?: throw new CryptoException('Invalid cipher algo: ' . openssl_error_string());
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        return openssl_decrypt($encrypted, self::CIPHER_ALGO, $key, 0, $iv)
            ?: throw new CryptoException("Failed to decrypt string");
    }

    /**
     * Generates a secure random UUID (version 4)
     *
     * @return string
     * @throws CryptoException
     */
    public static function uuid(): string
    {
        return Uuid::v4();
    }

    protected static function getHashOptions(): array
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return [PASSWORD_ARGON2ID, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
                'threads' => PASSWORD_ARGON2_DEFAULT_THREADS,
            ]];
        } else {
            return [PASSWORD_BCRYPT, [
                'cost' => max(PASSWORD_BCRYPT_DEFAULT_COST, 12),
            ]];
        }
    }
}