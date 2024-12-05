<?php

namespace SugoiCloud\Common;

use InvalidArgumentException;
use SugoiCloud\Common\Exceptions\CryptoException;

/**
 * UUID generation and validation functions.
 */
class Uuid
{
    /**
     * Generates a UUID v1 (time-based UUID)
     *
     * Simplified version, uses random bytes instead of device MAC addresses.
     *
     * @return string
     * @throws CryptoException
     */
    public static function v1(): string
    {
        if (self::useRamsey()) {
            return \Ramsey\Uuid\Uuid::uuid1()->toString();
        }

        // 60-bit timestamp based on the number of 100-nanosecond intervals since UUID epoch (October 15, 1582)
        $time = microtime(true) * 10000000 + 0x01B21DD213814000;

        $timeHex = str_pad(dechex($time), 16, '0', STR_PAD_LEFT);
        $timeLow = substr($timeHex, 8, 8);
        $timeMid = substr($timeHex, 4, 4);
        $timeHiAndVersion = dechex((hexdec(substr($timeHex, 0, 4)) & 0x0FFF) | 0x1000);

        // Generate clock sequence and node (use random values for simplicity)
        $clockSeq = Crypto::random(2);
        $clockSeq = dechex((hexdec($clockSeq) & 0x3FFF) | 0x8000); // Set variant bits
        $node = Crypto::random(6); // Generate random node

        return sprintf('%08s-%04s-%04s-%04s-%012s', $timeLow, $timeMid, $timeHiAndVersion, $clockSeq, $node);
    }

    /**
     * Generates a UUID v4 (randomly generated UUID)
     *
     * @return string
     * @throws CryptoException
     */
    public static function v4(): string
    {
        if (self::useRamsey()) {
            return \Ramsey\Uuid\Uuid::uuid4()->toString();
        }

        $data = Crypto::generate(16);
        assert(strlen($data) == 16);

        // Set version to 0100 (UUID v4)
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        // Set the two most significant bits to 10
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Generates a UUID v5 (namespaced, SHA-1 hash)
     *
     * @param string $namespace UUID namespace
     * @param string $name Name within the namespace
     * @return string
     */
    public static function v5(string $namespace, string $name): string
    {
        if (self::useRamsey()) {
            return \Ramsey\Uuid\Uuid::uuid5($namespace, $name)->toString();
        }

        if (!self::valid($namespace)) {
            throw new InvalidArgumentException("Invalid namespace UUID provided.");
        }

        // Convert namespace UUID to binary
        $namespaceBytes = hex2bin(str_replace(['-', '{', '}'], '', $namespace));
        $hash = sha1($namespaceBytes . $name);

        return sprintf(
            '%08s-%04s-%04x-%04x-%012s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            (hexdec(substr($hash, 12, 4)) & 0x0FFF) | 0x5000, // Set version to 0101
            (hexdec(substr($hash, 16, 4)) & 0x3FFF) | 0x8000, // Set variant bits
            substr($hash, 20, 12)
        );
    }

    /**
     * Generates a UUID v7 (time-ordered UUID)
     *
     * @return string
     * @throws CryptoException
     */
    public static function v7(): string
    {
        if (self::useRamsey()) {
            return \Ramsey\Uuid\Uuid::uuid7()->toString();
        }

        $time = (int)(microtime(true) * 1000);
        $time = str_pad(dechex($time), 12, '0', STR_PAD_LEFT);

        // 80 bits of randomness
        $data = Crypto::random(10);

        // Build the UUID with version and variant bits
        return sprintf(
            '%08s-%04s-7%03s-%04x-%012s',
            substr($time, 0, 8),
            substr($time, 8, 4),
            substr($data, 0, 3),
            (hexdec(substr($data, 3, 4)) & 0x3FFF) | 0x8000, // Set variant bits
            substr($data, 7, 12)
        );
    }

    /**
     * Generates a UUID v8 (custom data-based UUID)
     * @param string $data 122-bit custom data as a binary string or hex string
     * @return string
     * @throws InvalidArgumentException if custom data is not 122 bits (32 hex chars or 16 bytes)
     */
    public static function v8(string $data): string
    {
        if (self::useRamsey()) {
            return \Ramsey\Uuid\Uuid::uuid8($data)->toString();
        }

        // Validate custom data length
        $binaryData = self::getValidData($data);

        // Set version to 8 and the variant bits
        $binaryData[6] = chr((ord($binaryData[6]) & 0x0f) | 0x80); // Set version 8 (0b1000)
        $binaryData[8] = chr((ord($binaryData[8]) & 0x3f) | 0x80); // Set variant bits (10xx)

        // Format the custom data as a UUID string
        return sprintf(
            '%02x%02x%02x%02x-%02x%02x-%02x%02x-%02x%02x-%02x%02x%02x%02x%02x%02x',
            ord($binaryData[0]), ord($binaryData[1]), ord($binaryData[2]), ord($binaryData[3]),
            ord($binaryData[4]), ord($binaryData[5]),
            ord($binaryData[6]), ord($binaryData[7]),
            ord($binaryData[8]), ord($binaryData[9]),
            ord($binaryData[10]), ord($binaryData[11]), ord($binaryData[12]), ord($binaryData[13]),
            ord($binaryData[14]), ord($binaryData[15])
        );
    }

    /**
     * Validates if a given string is a valid UUID (version agnostic)
     *
     * @param string $uuid
     * @return bool
     */
    public static function valid(string $uuid): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $uuid) === 1;
    }

    /**
     * Parses a UUID string into its component parts.
     *
     * @param string $uuid
     * @return array|null
     */
    public static function parse(string $uuid): ?array
    {
        if (!self::valid($uuid)) {
            return null;
        }

        $parts = explode('-', $uuid);
        return [
            'time_low' => $parts[0],
            'time_mid' => $parts[1],
            'time_high_and_version' => $parts[2],
            'clock_seq_and_reserved' => $parts[3],
            'node' => $parts[4],
        ];
    }

    /**
     * Validates and converts the custom data to binary format
     *
     * @param string $data Custom data in hex or binary string
     * @return string Binary string of 16 bytes
     * @throws InvalidArgumentException if custom data is not the correct length
     */
    private static function getValidData(string $data): string
    {
        // If hex string, convert to binary and validate length
        if (ctype_xdigit($data) && strlen($data) === 32) {
            return hex2bin($data);
        }

        // If binary data, ensure it's 16 bytes (122 bits)
        if (strlen($data) === 16) {
            return $data;
        }

        throw new InvalidArgumentException("UUID v8 data must be 122 bits (32 hex chars or 16 bytes).");
    }

    private static bool $hasRamsey;

    private static function useRamsey(): bool
    {
        if (!isset(self::$hasRamsey)) {
            self::$hasRamsey = class_exists('Ramsey\Uuid\Uuid');
        }

        return self::$hasRamsey;
    }
}