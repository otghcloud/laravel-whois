<?php

use Carbon\Carbon;
use OTGH\LaravelWhois\Parsers\RDAPParser;

it('parses RDAP JSON correctly', function () {

    $json = [
        'ldhName' => 'example.com',
        'status' => ['active'],
        'events' => [
            ['eventAction' => 'registration', 'eventDate' => '2000-01-01T00:00:00Z'],
            ['eventAction' => 'expiration',   'eventDate' => '2030-01-01T00:00:00Z'],
        ],
        'entities' => [
            [
                'roles' => ['registrar'],
                'vcardArray' => [
                    'vcard',
                    [
                        ['version', [], 'text', '4.0'],
                        ['fn',      [], 'text', 'Example Registrar'],
                        ['url',     [], 'uri',  'https://registrar.example'],
                    ],
                ],
            ],
        ],
    ];

    $body = json_encode($json);

    $parser = new RDAPParser($body, $json, 200);
    $result = $parser->get();

    expect($result->domain)->toBe('example.com');
    expect($result->registered)->toBeTrue();
    expect($result->registrar)->toBe('Example Registrar');
    expect($result->registrarURL)->toBe('https://registrar.example');
    expect($result->expiresAt)->toBeInstanceOf(Carbon::class);
});
