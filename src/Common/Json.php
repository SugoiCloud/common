<?php

namespace SugoiCloud\Common;

use SugoiCloud\Common\Interfaces\JsonSerializable;

/**
 * JSON utilities.
 */
class Json
{
    public static function to($data): string
    {
        if ($data instanceof JsonSerializable) {
            return $data->toJson();
        } elseif (is_object($data)) {
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
        } elseif (is_null($data)) {
            return '';
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function from(string $data): array
    {
        return json_decode($data, true);
    }
}