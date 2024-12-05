<?php

namespace SugoiCloud\Common;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

/**
 * String manipulation and transformation utilities.
 */
class Str
{
    /**
     * Returns true if the string starts with one of the supplied prefixes.
     *
     * @param string $value
     * @param array|string $prefixes
     * @return bool
     */
    public static function hasPrefix(string $value, array|string $prefixes): bool
    {
        if (!is_iterable($prefixes)) {
            return str_starts_with($value, $prefixes);
        }

        foreach ($prefixes as $needle) {
            if (str_starts_with($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the string ends with one of the supplied suffixes.
     *
     * @param string $value
     * @param array|string $suffixes
     * @return bool
     */
    public static function hasSuffix(string $value, array|string $suffixes): bool
    {
        if (!is_iterable($suffixes)) {
            return str_ends_with($value, $suffixes);
        }

        foreach ($suffixes as $needle) {
            if (str_ends_with($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the string contains any of the supplied contents.
     *
     * @param string $value
     * @param array|string $contents
     * @return bool
     */
    public static function contains(string $value, array|string $contents): bool
    {
        if (!is_iterable($contents)) {
            return str_contains($value, $contents);
        }

        foreach ($contents as $content) {
            if (str_contains($value, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds the specified prefix to the string.
     *
     * @param string $value
     * @param string $prefix
     * @return string
     */
    public static function prefix(string $value, string $prefix): string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }

    /**
     * Adds the specified suffix to the string.
     *
     * @param string $value
     * @param string $suffix
     * @return string
     */
    public static function suffix(string $value, string $suffix): string
    {
        $quoted = preg_quote($suffix, '/');

        return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $suffix;
    }

    /**
     * Does a "best effort" split on the string based on casing boundaries and whitespaces.
     *
     * @param string $value
     * @return array
     */
    public static function splitCase(string $value): array
    {
        return preg_split(
            '/(?<=[a-z0-9])(?=[A-Z])|[_\-\s]+/',
            $value, -1, PREG_SPLIT_NO_EMPTY
        );
    }

    /**
     * Converts a string to `UPPER CASE`.
     *
     * @param string $value
     * @return string
     */
    public static function toUpperCase(string $value): string
    {
        return mb_convert_case($value, MB_CASE_UPPER, 'UTF-8');
    }

    /**
     * Converts a string to `lower case`.
     *
     * @param string $value
     * @return string
     */
    public static function toLowerCase(string $value): string
    {
        return mb_convert_case($value, MB_CASE_LOWER, 'UTF-8');
    }

    /**
     * Converts a string to `Title Case`.
     *
     * @param string $value
     * @return string
     */
    public static function toTitleCase(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Converts a string to `Sentence case`.
     *
     * @param string $value
     * @return string
     */
    public static function toSentenceCase(string $value): string
    {
        return mb_ucfirst(static::toLowerCase($value), 'UTF-8');
    }

    /**
     * Converts a string to `PascalCase` aka `StudlyCase`.
     *
     * @param string $value
     * @return string
     */
    public static function toPascalCase(string $value): string
    {
        return str_replace(' ', '', ucwords(
            implode(' ', static::splitCase($value))
        ));
    }

    /**
     * Converts a string into `snake_case`.
     *
     * @param string $value
     * @return string
     */
    public static function toSnakeCase(string $value): string
    {
        return ctype_lower($value) ? $value : strtolower(
            implode('_', static::splitCase($value))
        );
    }

    /**
     * Converts a string into `SNAKE_SNAAAAAAAAAAKEEEEE_CASE`.
     *
     * @param string $value
     * @return string
     */
    public static function toConstCase(string $value): string
    {
        return ctype_upper($value) ? $value : strtoupper(
            implode('_', static::splitCase($value))
        );
    }

    /**
     * Converts a string into `kebab-case`.
     *
     * @param string $value
     * @return string
     */
    public static function toKebabCase(string $value): string
    {
        return ctype_lower($value) ? $value : strtolower(
            implode('-', static::splitCase($value))
        );
    }

    /**
     * Converts a string into `camelCase`.
     *
     * @param string $value
     * @return string
     */
    public static function toCamelCase(string $value): string
    {
        return ctype_lower($value) ? $value : mb_lcfirst(static::toPascalCase($value));
    }

    public static function trim(string $value): string
    {
        return trim($value);
    }

    public static function trimStart(string $value): string
    {
        return ltrim($value);
    }

    public static function trimEnd(string $value): string
    {
        return rtrim($value);
    }

    public static function pad(string $value, int $length, string $character = ' '): string
    {
        return mb_str_pad($value, $length, $character, STR_PAD_BOTH);
    }

    public static function padStart(string $value, int $length, string $character = ' '): string
    {
        return mb_str_pad($value, $length, $character, STR_PAD_LEFT);
    }

    public static function padEnd(string $value, int $length, string $character = ' '): string
    {
        return mb_str_pad($value, $length, $character, STR_PAD_RIGHT);
    }

    public static function pluralize(string $value): string
    {
        if (!isset(self::$inflector)) {
            self::$inflector = InflectorFactory::create()->build();
        }

        return self::$inflector->pluralize($value);
    }

    public static function singularize(string $value): string
    {
        if (!isset(self::$inflector)) {
            self::$inflector = InflectorFactory::create()->build();
        }

        return self::$inflector->singularize($value);
    }

    private static Inflector $inflector;
}