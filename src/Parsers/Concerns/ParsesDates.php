<?php

namespace OTGH\LaravelWhois\Parsers\Concerns;

use Carbon\Carbon;
use OTGH\LaravelWhois\Parsers\ParsedResult;

trait ParsesDates
{
    protected function parseAnyDate(?string $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        $date = trim($date);

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
        }

        $formats = [
            'Y-m-d\TH:i:s\Z',
            'Y-m-d\TH:i:s.u\Z',
            'Y-m-d H:i:s',
            'd-M-Y',
            'd.m.Y',
            'M j Y',
            'j-M-Y',
            'Y/m/d',
            'Y.m.d',
        ];

        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $date);
            } catch (\Throwable) {
            }
        }

        if (preg_match('/(\d{1,2})-([A-Za-z]{3})-(\d{4})/', $date, $m)) {
            return Carbon::createFromFormat('d-M-Y', "{$m[1]}-{$m[2]}-{$m[3]}");
        }

        $clean = preg_replace('/[^0-9A-Za-z:\/\-\s\.TZ]/', ' ', $date);

        try {
            return Carbon::parse($clean);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseDatesFromRdap(array $json, ParsedResult $r): void
    {
        foreach ($json['events'] ?? [] as $event) {

            if (! isset($event['eventAction'], $event['eventDate'])) {
                continue;
            }

            $date = Carbon::parse($event['eventDate']);

            match (strtolower($event['eventAction'])) {
                'registration' => $r->createdAt = $date,
                'last changed' => $r->updatedAt = $date,
                'expiration' => $r->expiresAt = $date,
                default => null,
            };
        }
    }

    protected function parseDatesFromWhois(string $raw, array $patterns, ParsedResult $r): void
    {
        foreach ($patterns['created'] as $pattern) {
            if (preg_match("/{$pattern}/i", $raw, $m)) {
                $r->createdAt = $this->parseAnyDate($m[1]);
                break;
            }
        }

        foreach ($patterns['updated'] as $pattern) {
            if (preg_match("/{$pattern}/i", $raw, $m)) {
                $r->updatedAt = $this->parseAnyDate($m[1]);
                break;
            }
        }

        foreach ($patterns['expiry'] as $pattern) {
            if (preg_match("/{$pattern}/i", $raw, $m)) {
                $r->expiresAt = $this->parseAnyDate($m[1]);
                break;
            }
        }
    }
}
