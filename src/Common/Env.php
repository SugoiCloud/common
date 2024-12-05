<?php

namespace SugoiCloud\Common;

/**
 * Environment variable management.
 */
class Env
{
    /**
     * Check if an environment variable is set
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return getenv($key) !== false;
    }

    /**
     * Get environment variable.
     *
     * @param string $key
     * @param mixed|null $default
     * @return string|null
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return self::has($key) ? getenv($key) : $default;
    }

    /**
     * Set environment variable.
     *
     * @param string $key
     * @param string $value
     */
    public static function set(string $key, string $value): void
    {
        putenv("$key=$value");
    }

    /**
     * Get all environment variables.
     *
     * @return array
     */
    public static function all(): array
    {
        return getenv();
    }
}