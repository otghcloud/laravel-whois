<?php

use OTGH\LaravelWhois\Exceptions\RdapServerNotSupportedException;
use OTGH\LaravelWhois\Lookup\LookupManager;

it('throws exceptions in strict mode', function () {
    config(['whois.lookup_mode' => 'strict']);

    $manager = new LookupManager('invalid.tldd', ['rdap']);

    $this->expectException(RdapServerNotSupportedException::class);

    $manager->run();
});
