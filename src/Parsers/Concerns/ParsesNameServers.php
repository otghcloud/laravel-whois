<?php

namespace OTGH\LaravelWhois\Parsers\Concerns;

use OTGH\LaravelWhois\Parsers\ParsedResult;

trait ParsesNameServers
{
    protected function parseNameserversFromRdap(array $json, ParsedResult $r): void
    {
        foreach ($json['nameservers'] ?? [] as $ns) {
            if (! empty($ns['ldhName'])) {
                $r->nameServers[] = strtolower(trim($ns['ldhName']));
            }
        }

        $r->nameServers = array_values(array_unique($r->nameServers));
    }

    protected function parseNameserversFromWhois(string $raw, array $patterns, ParsedResult $r): void
    {
        foreach ($patterns['single'] as $pattern) {
            if (preg_match_all("/{$pattern}/i", $raw, $matches)) {
                foreach ($matches[1] as $ns) {
                    $r->nameServers[] = strtolower(trim($ns));
                }
            }
        }

        foreach ($patterns['block'] as $pattern) {
            if (preg_match("/{$pattern}/is", $raw, $block)) {
                $lines = array_filter(array_map('trim', explode("\n", trim($block[1]))));
                foreach ($lines as $ns) {
                    $r->nameServers[] = strtolower(trim($ns));
                }
            }
        }

        $r->nameServers = array_values(array_unique($r->nameServers));
    }
}
