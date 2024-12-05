<?php

namespace SugoiCloud\Common\Exceptions;

/**
 * Thrown if authentication fails for any reason, i.e. HTTP status 401.
 */
class AuthenticationException extends Exception
{
    public function __construct(string $message = "Unauthenticated", int $code = 401)
    {
        parent::__construct($message, $code);
    }
}