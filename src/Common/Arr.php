<?php

namespace SugoiCloud\Common;

/**
 * Array manipulation utilities.
 */
class Arr
{
    public static function wrap($value): array
    {
        return is_array($value ?? []) ? $value : [$value];
    }

    /**
     * @template T
     * @param array<T> $value
     * @return T
     */
    public static function first(array $value): null
    {
        return $value[0] ?? null;
    }
}