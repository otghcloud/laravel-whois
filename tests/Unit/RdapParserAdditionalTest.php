<?php

use OTGH\LaravelWhois\Parsers\RDAPParser;

it('parses RDAP without registrar entity', function () {
    $json = [
        'ldhName' => 'example.net',
        'status' => ['active'],
        'events' => [],
        'entities' => [],
    ];

    $body = json_encode($json);
    $result = (new RDAPParser($body, $json, 200))->get();

    expect($result->domain)->toBe('example.net');
    expect($result->registrar)->toBe('');
    expect($result->registrarURL)->toBeNull();
});

it('parses RDAP registrar using org when fn missing', function () {
    $json = [
        'ldhName' => 'example.org',
        'entities' => [[
            'roles' => ['registrar'],
            'vcardArray' => [
                'vcard',
                [
                    ['org', [], 'text', 'My Registrar Ltd'],
                    ['url', [], 'uri', 'https://my-registrar.test'],
                ],
            ],
        ]],
    ];

    $body = json_encode($json);
    $result = (new RDAPParser($body, $json, 200))->get();

    expect($result->registrar)->toBe('My Registrar Ltd');
    expect($result->registrarURL)->toBe('https://my-registrar.test');
});

it('handles RDAP entities missing vcardArray gracefully', function () {
    $json = [
        'ldhName' => 'example.dev',
        'entities' => [
            [
                'roles' => ['registrar'],
                // missing vcardArray
            ],
        ],
    ];

    $body = json_encode($json);
    $result = (new RDAPParser($body, $json, 200))->get();

    expect($result->registrar)->toBe('');
    expect($result->registrarURL)->toBeNull();
});
