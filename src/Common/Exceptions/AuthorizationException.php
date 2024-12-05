<?php

namespace SugoiCloud\Common\Exceptions;

/**
 * Thrown if authorization fails for any reason, i.e. HTTP Status 403.
 */
class AuthorizationException extends AuthenticationException
{
    public function __construct(string $message = "Unauthorized")
    {
        parent::__construct($message, 403);
    }
}