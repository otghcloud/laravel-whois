<?php

namespace OTGH\LaravelWhois\Parsers\Concerns;

use OTGH\LaravelWhois\Parsers\ParsedResult;

trait ParsesStatuses
{
    protected function parseStatus(array $statuses, ParsedResult $r): void
    {
        foreach ($statuses as $s) {
            $r->status[] = strtolower(trim($s));
        }

        $r->status = array_values(array_unique($r->status));
    }

    protected function parseWhoisStatus(string $raw, array $patterns, ParsedResult $r): void
    {
        foreach ($patterns['status'] as $pattern) {
            if (preg_match_all("/{$pattern}/i", $raw, $matches)) {
                foreach ($matches[1] as $st) {
                    $r->status[] = strtolower(trim($st));
                }
            }
        }

        $r->status = array_unique($r->status);
    }
}
