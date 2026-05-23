<?php

namespace OTGH\LaravelWhois\Lookup\Strategy;

use OTGH\LaravelWhois\Clients\WhoisClient;
use OTGH\LaravelWhois\Domain\DomainInfo;
use OTGH\LaravelWhois\Exceptions\WhoisConnectionException;
use OTGH\LaravelWhois\Exceptions\WhoisMalformedResponseException;
use OTGH\LaravelWhois\Exceptions\WhoisRateLimitException;
use OTGH\LaravelWhois\Parsers\WhoisParser;
use OTGH\LaravelWhois\Support\Retry;

class WhoisStrategy
{
    /**
     * Loaded from whois-patterns.json
     */
    private array $patterns = [];

    public function __construct()
    {
        $this->loadPatterns();
    }

    protected function loadPatterns(): void
    {
        $base = __DIR__.'/../../Data/';

        $json = file_get_contents($base.'whois-patterns.json');
        $this->patterns = json_decode($json, true) ?: [];
    }

    /**
     * Perform a WHOIS lookup.
     */
    public function lookup(DomainInfo $info)
    {
        $config = config('whois');
        $attempts = $config['retry_attempts'] ?? 1;
        $backoff = $config['retry_backoff'] ?? [];

        try {
            $raw = Retry::run($attempts, $backoff, function () use ($info) {
                $client = new WhoisClient(
                    $info->registrable,
                    $info->extension,
                    $info->extensionTop
                );

                return $client->getData();
            });
        } catch (\Throwable $e) {
            throw new WhoisConnectionException($e->getMessage());
        }

        if (! is_string($raw) || trim($raw) === '') {
            throw new WhoisConnectionException('Empty WHOIS response');
        }

        if ($this->matchesRateLimitPattern($raw)) {
            throw new WhoisRateLimitException('WHOIS rate limit exceeded');
        }

        // Parse WHOIS
        $parsed = (new WhoisParser($raw))->get();
        $parsed->source = 'whois';
        $parsed->rawResponse = $raw;

        if (! $parsed->domain) {
            throw new WhoisMalformedResponseException(
                "Malformed WHOIS for '{$info->registrable}'"
            );
        }

        return $parsed;
    }

    /**
     * Only treat *true* rate‑limit messages as limits.
     */
    protected function matchesRateLimitPattern(string $raw): bool
    {
        $patterns = $this->patterns['rate_limit'] ?? [];
        $rawLower = strtolower($raw);

        foreach ($patterns as $pattern) {
            if (@preg_match("/{$pattern}/i", $rawLower)) {
                if (preg_match("/{$pattern}/i", $rawLower)) {
                    return true;
                }
            }
        }

        return false;
    }
}
