<?php

namespace SugoiCloud\Common;

/**
 * URI/Path transform/parsing utilities.
 */
class Path
{
    /**
     * Joins multiple path segments into a single path.
     *
     * @param string ...$segments
     * @return string
     */
    public static function join(string ...$segments): string
    {
        return self::normalize(implode(DIRECTORY_SEPARATOR, array_filter(array_map(
            fn($path) => mb_trim($path, " \t\n\r\0\x0B/\\"), $segments
        ), 'strlen')));
    }

    /**
     * Normalizes a path, removes redundant, /./ and /../ path components.
     * Unlike {@link realpath()}, the path does not have to exist.
     *
     * @param string $path
     * @return string
     */
    public static function normalize(string $path): string
    {
        $parts = [];
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);
            } else {
                $parts[] = $segment;
            }
        }

        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Returns the directory component of a path.
     *
     * @param string $path
     * @return string
     */
    public static function dirname(string $path): string
    {
        return dirname($path);
    }

    /**
     * Returns the filename component of a path.
     *
     * @param string $path
     * @param bool $ext Include the file extension in the filename.
     * @return string
     */
    public static function filename(string $path, bool $ext = true): string
    {
        return $ext ? basename($path) : pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Returns the file extension of a path.
     *
     * @param string $path
     * @return string
     */
    public static function ext(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Determines if a path is absolute.
     *
     * @param string $path
     * @return bool
     */
    public static function isAbsolute(string $path): bool
    {
        return DIRECTORY_SEPARATOR === '/'
            ? str_starts_with($path, DIRECTORY_SEPARATOR)
            : preg_match('/^[A-Z]:[\/\\\\]/i', $path);
    }

    public static function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public static function isWritable(string $path): bool
    {
        return is_writeable($path);
    }

    public static function isExecutable(string $path): bool
    {
        return is_executable($path);
    }

    /**
     * Check if the path can be resolved to a canonical realpath within the provided $base directory.
     * Used for preventing directory traversal.
     *
     * @param string $path
     * @param string $base
     * @return bool
     */
    public static function isWithin(string $path, string $base): bool
    {
        $path = realpath($path);
        $base = realpath($base);

        return $path !== false && $base !== false && str_starts_with($path, $base);
    }

    /**
     * Make a canonical realpath resolved to be within the provided $base directory.
     * Used for preventing directory traversal.
     *
     * @param string $path
     * @param string $base
     * @return string
     * @throws \InvalidArgumentException if the provided path does not exist within the base directory.
     */
    public static function within(string $path, string $base): string
    {
        if (!str_starts_with($path, $base)) {
            $path = static::join($base, $path);
        }

        if (!static::isWithin($path, $base)) {
            throw new \InvalidArgumentException("Path '{$path}' not within base directory.");
        }

        return $path;
    }
}