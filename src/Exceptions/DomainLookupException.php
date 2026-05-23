<?php

namespace OTGH\LaravelWhois\Exceptions;

use RuntimeException;

abstract class DomainLookupException extends RuntimeException
{
    public function __construct(
        string $message,
        protected string $source
    ) {
        parent::__construct($message);
    }

    public function source(): string
    {
        return $this->source;
    }
}
