<?php

namespace SugoiCloud\Common\Exceptions;

class ValidationException extends Exception
{
    public function __construct(
        public readonly array $errors
    )
    {
        parent::__construct('Validation failed');
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'errors' => $this->errors,
        ]);
    }
}