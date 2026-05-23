<?php

namespace OTGH\LaravelWhois\Exceptions;

abstract class WhoisException extends DomainLookupException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 'whois');
    }
}
