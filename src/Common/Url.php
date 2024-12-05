<?php

namespace SugoiCloud\Common;

/**
 * URL builders and parsing utilities.
 */
class Url
{
    /**
     * Join URL path segments into a relative URL path.
     *
     * @param string ...$segments
     * @return string
     */
    public static function join(string ...$segments): string
    {
        return implode('/', array_filter(array_map(
            fn($path) => mb_trim($path, " \t\n\r\0\x0B/\\"), $segments
        ), 'strlen'));
    }

    /**
     * Join URL path segments into an absolute URL path.
     *
     * @param string ...$segments
     * @return string
     */
    public static function abs(string ...$segments): string
    {
        return '/' . self::join(...$segments);
    }
}