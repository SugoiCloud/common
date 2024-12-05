<?php

namespace SugoiCloud\Common\Exceptions;

use SugoiCloud\Common\Interfaces\ArraySerializable;
use SugoiCloud\Common\Interfaces\JsonSerializable;
use SugoiCloud\Common\Json;

/**
 * General framework exception, inherited by all Exception within the Sugoi framework.
 */
class Exception extends \Exception implements ArraySerializable, JsonSerializable, \JsonSerializable
{
    public function __construct(
        string      $message = "",
        int         $code = 0,
        ?\Throwable $previous = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
        ];
    }

    public function toJson(): string
    {
        return Json::to($this->toArray());
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}