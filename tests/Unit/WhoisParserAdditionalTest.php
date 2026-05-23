<?php

use OTGH\LaravelWhois\Parsers\WhoisParser;

it('parses WHOIS dates in multiple formats', function () {

    $raw = <<<'WHOIS'
Domain Name: example.com
Creation Date: 01-Jan-2020
Updated Date: 2021/02/02
Expiry Date: 2030-03-03T00:00:00Z
WHOIS;

    $result = (new WhoisParser($raw))->get();

    expect($result->createdAt->year)->toBe(2020);
    expect($result->updatedAt->year)->toBe(2021);
    expect($result->expiresAt->year)->toBe(2030);
});

it('detects unregistered WHOIS results', function () {
    $raw = 'No match for domain "unknown-abc12345.com"';

    $result = (new WhoisParser($raw))->get();

    expect($result->registered)->toBeFalse();
});

it('parses block-format nameservers', function () {

    $raw = <<<'WHOIS'
Name servers:
  NS1.EXAMPLE.COM
  NS2.EXAMPLE.COM
WHOIS;

    $result = (new WhoisParser($raw))->get();

    expect($result->nameServers)->toBe(['ns1.example.com', 'ns2.example.com']);
});
