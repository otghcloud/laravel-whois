<?php

use OTGH\LaravelWhois\Facades\LaravelWhois;
use OTGH\LaravelWhois\RDAP;
use Tests\Stubs\RdapClientStub;

it('resolves through Laravel facade', function () {

    $this->app->instance(
        RDAP::class,
        new RdapClientStub(200, json_encode(['ldhName' => 'example.com']))
    );

    $result = LaravelWhois::lookup('example.com');

    expect($result->domain)->toBe('example.com');
});
