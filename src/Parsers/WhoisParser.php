<?php

namespace OTGH\LaravelWhois\Parsers;

use OTGH\LaravelWhois\Parsers\Concerns\ParsesDates;
use OTGH\LaravelWhois\Parsers\Concerns\ParsesDomain;
use OTGH\LaravelWhois\Parsers\Concerns\ParsesNameServers;
use OTGH\LaravelWhois\Parsers\Concerns\ParsesRegistrar;
use OTGH\LaravelWhois\Parsers\Concerns\ParsesStatuses;

class WhoisParser extends BaseParser
{
    use ParsesDates;
    use ParsesDomain;
    use ParsesNameServers;
    use ParsesRegistrar;
    use ParsesStatuses;

    private array $patterns;

    public function __construct(string $raw)
    {
        $this->loadPatterns();
        parent::__construct($raw);
    }

    private function loadPatterns(): void
    {
        $base = __DIR__.'/../Data/';
        $this->patterns = [
            'dates' => json_decode(file_get_contents($base.'whois-dates.json'), true),
            'nameservers' => json_decode(file_get_contents($base.'whois-nameservers.json'), true),
            'status' => json_decode(file_get_contents($base.'whois-status.json'), true),
            'availability' => json_decode(file_get_contents($base.'whois-availability.json'), true),
        ];
    }

    protected function parse(): void
    {
        $raw = $this->raw;
        $r = $this->result;

        $r->rawResponse = $raw;

        $this->parseDomainFromWhois($raw, $r);
        $this->parseRegistrarFromWhois($raw, $r);
        $this->parseDatesFromWhois($raw, $this->patterns['dates'], $r);
        $this->parseNameserversFromWhois($raw, $this->patterns['nameservers'], $r);
        $this->parseWhoisStatus($raw, $this->patterns['status'], $r);

        $lower = strtolower($raw);
        $available = false;

        foreach ($this->patterns['availability']['available'] as $word) {
            if (str_contains($lower, $word)) {
                $available = true;
                break;
            }
        }

        if ($available) {
            $r->registered = false;
        } else {
            $r->registered = (
                $r->domain ||
                $r->registrar ||
                $r->createdAt ||
                $r->expiresAt ||
                ! empty($r->nameServers)
            );
        }
    }
}
