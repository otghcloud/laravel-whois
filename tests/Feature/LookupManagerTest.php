<?php

use OTGH\LaravelWhois\Lookup\LookupManager;
use OTGH\LaravelWhois\RDAP;
use OTGH\LaravelWhois\WHOIS;
use Tests\Stubs\RdapClientStub;
use Tests\Stubs\WhoisClientStub;

it('short-circuits when RDAP succeeds', function () {

    $this->app->instance(
        RDAP::class,
        new RdapClientStub(200, json_encode(['ldhName' => 'example.com']))
    );

    // WHOIS client won't be used
    $this->app->instance(
        WHOIS::class,
        new WhoisClientStub('should not run')
    );

    $manager = new LookupManager('example.com', ['rdap', 'whois']);
    $result = $manager->run();

    expect($result->domain)->toBe('example.com');
});
