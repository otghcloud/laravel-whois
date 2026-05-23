<?php

use Carbon\Carbon;
use OTGH\LaravelWhois\Parsers\WhoisParser;

it('parses raw WHOIS response', function () {

    $raw = <<<'WHOIS'
Domain Name: example.com
Registrar: EXAMPLE REGISTRAR INC.
Creation Date: 2000-01-01T00:00:00Z
Registry Expiry Date: 2030-01-01T00:00:00Z
WHOIS;

    $parser = new WhoisParser($raw);
    $result = $parser->get();

    expect($result->domain)->toBe('example.com');
    expect($result->registered)->toBeTrue();
    expect($result->registrar)->toBe('EXAMPLE REGISTRAR INC.');
    expect($result->expiresAt)->toBeInstanceOf(Carbon::class);
});
