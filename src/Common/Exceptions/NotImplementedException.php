<?php

namespace SugoiCloud\Common\Exceptions;

/**
 * Generic "Not implemented" exception for convenience.
 */
class NotImplementedException extends Exception
{
    public function __construct(string $message = "Not implemented")
    {
        parent::__construct($message, 501);
    }
}