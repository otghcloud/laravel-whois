<?php

namespace OTGH\LaravelWhois\Lookup\Strategy;

use OTGH\LaravelWhois\Clients\RdapClient;
use OTGH\LaravelWhois\Domain\DomainInfo;
use OTGH\LaravelWhois\Exceptions\RdapConnectionException;
use OTGH\LaravelWhois\Exceptions\RdapMalformedResponseException;
use OTGH\LaravelWhois\Exceptions\RdapRateLimitException;
use OTGH\LaravelWhois\Exceptions\RdapServerNotSupportedException;
use OTGH\LaravelWhois\Parsers\RDAPParser;
use OTGH\LaravelWhois\Support\Retry;

class RdapStrategy
{
    public function lookup(DomainInfo $info)
    {
        $servers = RdapClient::servers();
        $ext = strtolower($info->extension);
        $extTop = strtolower($info->extensionTop);

        if (! isset($servers[$ext]) && ! isset($servers[$extTop])) {
            throw new RdapServerNotSupportedException(
                "RDAP not supported for TLD '{$info->extension}'"
            );
        }

        $config = config('whois');
        $attempts = $config['retry_attempts'] ?? 1;
        $backoff = $config['retry_backoff'] ?? [];

        try {
            [$code, $raw] = Retry::run($attempts, $backoff, function () use ($info) {
                $client = new RdapClient(
                    $info->registrable,
                    $info->extension,
                    $info->extensionTop
                );

                return $client->getData();
            });
        } catch (\Throwable $e) {
            throw new RdapConnectionException('RDAP connection failed: '.$e->getMessage());
        }

        if ($code === 429) {
            throw new RdapRateLimitException('RDAP rate limit exceeded');
        }

        $json = json_decode($raw, true);

        if (! is_array($json)) {
            throw new RdapMalformedResponseException('RDAP invalid JSON response');
        }

        // NON-RDAP JSON (e.g. Spring Boot style API errors)
        if (
            isset($json['status']) &&
            isset($json['error']) &&
            isset($json['path'])
        ) {
            throw new RdapMalformedResponseException(
                'RDAP endpoint returned non‑RDAP error JSON'
            );
        }

        // Standard RDAP error response
        if (
            isset($json['errorCode']) ||
            isset($json['title']) ||
            isset($json['description'])
        ) {
            throw new RdapMalformedResponseException(
                'RDAP server returned error document instead of domain data'
            );
        }

        // Some registries use { code: 404, message: "Domain not found" }
        if (
            (isset($json['code']) && is_numeric($json['code'])) &&
            isset($json['message'])
        ) {
            throw new RdapMalformedResponseException(
                'RDAP server returned non-domain error structure'
            );
        }

        if (! isset($json['ldhName'])) {
            throw new RdapMalformedResponseException(
                'RDAP response missing required domain property (ldhName)'
            );
        }

        $parsed = (new RDAPParser($raw, $json, $code))->get();
        $parsed->source = 'rdap';

        return $parsed;
    }
}
