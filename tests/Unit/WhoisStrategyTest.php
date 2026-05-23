<?php

use OTGH\LaravelWhois\Clients\WhoisClient;
use OTGH\LaravelWhois\Domain\DomainInfo;
use OTGH\LaravelWhois\Lookup\Strategy\WhoisStrategy;
use Tests\Stubs\WhoisClientStub;

it('executes WHOIS strategy and returns parsed result', function () {

    $stub = new WhoisClientStub(
        "Domain Name: example.com\nRegistrar: REG\nCreation Date: 2000-01-01T00:00:00Z"
    );

    $this->app->instance(WhoisClient::class, $stub);

    $strategy = new WhoisStrategy;

    $info = new DomainInfo(
        registrable: 'example.com',
        extension: 'com',
        extensionTop: 'com',
        inputDomain: 'example.com'
    );

    $result = $strategy->lookup($info);

    expect($result->domain)->toBe('example.com');
    expect($result->registered)->toBeTrue();
});
