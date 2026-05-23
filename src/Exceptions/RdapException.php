<?php

namespace OTGH\LaravelWhois\Exceptions;

abstract class RdapException extends DomainLookupException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 'rdap');
    }
}
