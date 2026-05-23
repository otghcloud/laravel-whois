<?php

use OTGH\LaravelWhois\Clients\RdapClient;
use OTGH\LaravelWhois\Domain\DomainInfo;
use OTGH\LaravelWhois\Exceptions\RdapRateLimitException;
use OTGH\LaravelWhois\Lookup\Strategy\RdapStrategy;
use Tests\Stubs\RdapClientStub;

it('throws RdapRateLimitException', function () {

    app()->instance(RdapClient::class, new RdapClientStub(
        domain: 'example.com',
        extension: 'com',
        extensionTop: null,
        overrideServer: '',
        code: 429,
        raw: ''
    ));

    $strategy = new RdapStrategy;

    $this->expectException(RdapRateLimitException::class);

    $strategy->lookup(new DomainInfo('example.com', 'com'));
});
