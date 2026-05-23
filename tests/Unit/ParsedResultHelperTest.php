<?php

use OTGH\LaravelWhois\Parsers\ParsedResult;

it('fetchDates returns correct keys', function () {
    $r = new ParsedResult;

    $dates = $r->fetchDates();

    expect($dates)->toHaveKeys(['created', 'updated', 'expires']);
});

it('isExpired works correctly', function () {
    $r = new ParsedResult;
    $r->expiresAt = now()->subDay();

    expect($r->isExpired())->toBeTrue();
});

it('usesRdap and usesWhois helpers work', function () {
    $r = new ParsedResult('');

    $r->source = 'rdap';
    expect($r->usesRdap())->toBeTrue();
    expect($r->usesWhois())->toBeFalse();

    $r->source = 'whois';
    expect($r->usesRdap())->toBeFalse();
    expect($r->usesWhois())->toBeTrue();
});

it('renders toJson correctly', function () {
    $r = new ParsedResult('raw');
    $json = $r->toJson();

    expect($json)->toBeJson();
});
