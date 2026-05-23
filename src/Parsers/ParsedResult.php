<?php

namespace OTGH\LaravelWhois\Parsers;

use Carbon\Carbon;
use OTGH\LaravelWhois\Exceptions\DomainLookupException;

class ParsedResult
{
    public string $domain = '';

    public bool $registered = false;

    public string $registrar = '';

    public ?string $registrarURL = null;

    public ?Carbon $createdAt = null;

    public ?Carbon $updatedAt = null;

    public ?Carbon $expiresAt = null;

    public array $status = [];

    public array $nameServers = [];

    public int $ageSeconds = 0;

    public int $remainingSeconds = 0;

    public bool $grace = false;

    public bool $redemption = false;

    public bool $pendingDelete = false;

    public array $unknown = [];

    public string $rawResponse = '';

    public string $source = 'unknown';

    public bool $servedFromCache = false;

    public array $errors = [];

    public function __construct(string $rawResponse = '')
    {
        $this->rawResponse = $rawResponse;
    }

    public function addError(\Throwable $e): void
    {
        $source = $e instanceof DomainLookupException
            ? $e->source()
            : 'unknown';

        $this->errors[] = [
            'source' => $source,
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /* ---------------------------
       Helper Methods
    ----------------------------*/

    public function fetchDates(): array
    {
        return [
            'created' => $this->createdAt,
            'updated' => $this->updatedAt,
            'expires' => $this->expiresAt,
        ];
    }

    public function isRegistered(): bool
    {
        return (bool) $this->registered;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && now()->greaterThan($this->expiresAt);
    }

    public function daysUntilExpiry(): ?int
    {
        return $this->expiresAt ? now()->diffInDays($this->expiresAt, false) : null;
    }

    public function ageInDays(): ?int
    {
        return $this->createdAt ? $this->createdAt->diffInDays(now()) : null;
    }

    public function usesRdap(): bool
    {
        return $this->source === 'rdap';
    }

    public function usesWhois(): bool
    {
        return $this->source === 'whois';
    }

    public function toArray(): array
    {
        return [
            'domain' => $this->domain,
            'registered' => $this->registered,
            'source' => $this->source,
            'servedFromCache' => $this->servedFromCache,
            'registrar' => $this->registrar,
            'registrarURL' => $this->registrarURL,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'updatedAt' => $this->updatedAt?->toIso8601String(),
            'expiresAt' => $this->expiresAt?->toIso8601String(),
            'status' => $this->status,
            'nameServers' => $this->nameServers,
            'errors' => $this->errors,
            'rawResponse' => $this->rawResponse,
        ];
    }

    public function toJson(int $options = 0): string
    {
        return json_encode(
            $this->toArray(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | $options
        );
    }
}
