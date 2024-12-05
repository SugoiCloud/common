<?php

namespace SugoiCloud\Common;

use SugoiCloud\Common\Exceptions\CryptoException;

class Ulid
{
    /**
     * Crockford Base32 encoding characters for ULID.
     */
    private const BASE32_CHARS = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    /**
     * Generates a ULID.
     * @return string
     * @throws CryptoException
     */
    public static function generate(): string
    {
        // Get the current time in milliseconds as a 48-bit integer
        $time = (int)(microtime(true) * 1000);

        // Convert the time to a 10-character Base32 string
        $timeEncoded = self::encodeTime($time, 10);

        // Generate the 80-bit random component
        $randomEncoded = self::encodeRandom(16);

        // Combine the time and random components
        return $timeEncoded . $randomEncoded;
    }

    /**
     * Validates if a given string is a valid ULID.
     * @param string $ulid
     * @return bool
     */
    public static function valid(string $ulid): bool
    {
        return preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulid) === 1;
    }

    /**
     * Parses a ULID into its component parts.
     * @param string $ulid
     * @return array|null
     */
    public static function parse(string $ulid): ?array
    {
        if (!self::valid($ulid)) {
            return null;
        }

        $timePart = substr($ulid, 0, 10);
        $randomPart = substr($ulid, 10, 16);

        return [
            'timestamp' => self::decodeTime($timePart),
            'random' => $randomPart,
        ];
    }

    /**
     * Generates an 80-bit random component and encodes it as a 16-character Base32 string.
     *
     * @param int $length
     * @return string
     * @throws CryptoException
     */
    private static function encodeRandom(int $length): string
    {
        $randomBytes = Crypto::generate(10);
        $encoded = '';
        for ($i = 0; $i < strlen($randomBytes); $i++) {
            $encoded .= self::BASE32_CHARS[ord($randomBytes[$i]) % 32];
        }
        return substr($encoded, 0, $length);
    }

    /**
     * Encodes the timestamp into a 10-character Base32 string.
     * @param int $time
     * @param int $length
     * @return string
     */
    private static function encodeTime(int $time, int $length): string
    {
        $encoded = '';
        for ($i = 0; $i < $length; $i++) {
            $encoded = self::BASE32_CHARS[$time % 32] . $encoded;
            $time = intdiv($time, 32);
        }
        return $encoded;
    }

    /**
     * Decodes a 10-character Base32 ULID timestamp into milliseconds.
     *
     * @param string $timePart
     * @return int
     */
    private static function decodeTime(string $timePart): int
    {
        $time = 0;
        for ($i = 0; $i < strlen($timePart); $i++) {
            $time = $time * 32 + strpos(self::BASE32_CHARS, $timePart[$i]);
        }
        return $time;
    }
}