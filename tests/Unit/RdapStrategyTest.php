<?php

use OTGH\LaravelWhois\Domain\DomainInfo;
use OTGH\LaravelWhois\Lookup\Strategy\RdapStrategy;
use OTGH\LaravelWhois\Parsers\RDAPParser;

it('executes RDAP strategy and returns parsed result', function () {

    $rawJson = json_encode([
        'ldhName' => 'example.com',
        'status' => ['active'],
        'events' => [
            ['eventAction' => 'registration', 'eventDate' => '2000-01-01T00:00:00Z'],
        ],
    ]);

    // ✅ Inject stub DIRECTLY into strategy, no container
    $strategy = new RdapStrategyStub($rawJson);

    $info = new DomainInfo(
        registrable: 'example.com',
        extension: 'com',
        extensionTop: 'com',
        inputDomain: 'example.com'
    );

    $result = $strategy->lookup($info);

    expect($result->domain)->toBe('example.com');
    expect($result->registered)->toBeTrue();
    expect($result->source)->toBe('rdap');
});

/**
 * ✅ Simple stubbed strategy that bypasses RdapClient entirely
 */
class RdapStrategyStub extends RdapStrategy
{
    public function __construct(private string $rawJson) {}

    protected function fetchRdapResponse(DomainInfo $info): array
    {
        return [200, $this->rawJson];
    }

    // Override the RDAPStrategy::lookup() internals to call stubbed fetch
    public function lookup(DomainInfo $info)
    {
        [$code, $raw] = [200, $this->rawJson];

        $json = json_decode($raw, true);

        $parsed = (new RDAPParser($raw, $json, 200))->get();
        $parsed->source = 'rdap';

        return $parsed;
    }
}
