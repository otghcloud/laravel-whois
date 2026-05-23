<?php

namespace OTGH\LaravelWhois\Parsers\Concerns;

use OTGH\LaravelWhois\Parsers\ParsedResult;

trait ParsesDomain
{
    protected function parseDomainFromRdap(array $json, ParsedResult $r): void
    {
        $r->domain = strtolower($json['ldhName'] ?? '');
        $r->registered = $r->domain !== '';
    }

    protected function parseDomainFromWhois(string $raw, ParsedResult $r): void
    {
        if (preg_match('/Domain Name:\s*(.+)/i', $raw, $m)) {
            $r->domain = strtolower(trim($m[1]));
            $r->registered = true;
        }
    }
}
