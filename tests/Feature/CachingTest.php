<?php

use Illuminate\Support\Facades\Cache;
use OTGH\LaravelWhois\Clients\RdapClient;
use OTGH\LaravelWhois\Lookup\LookupManager;
use OTGH\LaravelWhois\Parsers\ParsedResult;
use Tests\Stubs\RdapClientStub;

it('returns cached results when available', function () {

    $cached = new ParsedResult('cached-raw');
    $cached->servedFromCache = false;
    $cached->domain = 'example.com';

    Cache::shouldReceive('get')
        ->once()
        ->andReturn($cached);

    $manager = new LookupManager('example.com');
    $result = $manager->run();

    expect($result->servedFromCache)->toBeTrue();
    expect($result->rawResponse)->toBe('cached-raw');
});

it('ignores cache when withoutCache is used', function () {

    Cache::shouldReceive('get')->never();
    Cache::shouldReceive('put')->never();

    app()->instance(RdapClient::class, new RdapClientStub(
        domain: 'example.com',
        extension: 'com',
        extensionTop: null,
        overrideServer: null,
        code: 200,
        raw: '{"ldhName":"example.com"}'
    ));

    $manager = (new LookupManager('example.com'))->withoutCache();
    $result = $manager->run();

    expect($result->servedFromCache)->toBeFalse();
    expect($result->domain)->toBe('example.com');
});
