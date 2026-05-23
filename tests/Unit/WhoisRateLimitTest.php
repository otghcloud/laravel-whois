<?php

use OTGH\LaravelWhois\Domain\DomainInfo;
use OTGH\LaravelWhois\Lookup\Strategy\WhoisStrategy;

it('detects real WHOIS rate limit responses', function () {
    $raw = 'Error: You have exceeded your query limit. Please slow down.';
    $strategy = new WhoisStrategy;
    $info = new DomainInfo('example.com', 'com', 'com', 'example.com');
    $ref = new ReflectionClass($strategy);
    $method = $ref->getMethod('matchesRateLimitPattern');
    $method->setAccessible(true);
    expect($method->invoke($strategy, $raw))->toBeTrue();
});

it('does not trigger rate limit for disclaimer text', function () {
    $raw = 'Access to the Whois and RDAP services is rate limited.';
    $strategy = new WhoisStrategy;
    $info = new DomainInfo('example.com', 'com', 'com', 'example.com');
    $ref = new ReflectionClass($strategy);
    $method = $ref->getMethod('matchesRateLimitPattern');
    $method->setAccessible(true);
    expect($method->invoke($strategy, $raw))->toBeFalse();
});
