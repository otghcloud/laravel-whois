<?php

namespace OTGH\LaravelWhois\Support;

use Illuminate\Support\Facades\Cache;
use OTGH\LaravelWhois\Parsers\ParsedResult;

trait CachesWhois
{
    protected function cacheKey(string $domain): string
    {
        return 'whois_cache:'.strtolower($domain);
    }

    protected function getCached(string $domain): ?ParsedResult
    {
        $enabled = config('whois.cache.enabled', false);

        if (! $enabled) {
            return null;
        }

        return Cache::get($this->cacheKey($domain));
    }

    protected function putCached(string $domain, ParsedResult $result): void
    {
        $enabled = config('whois.cache.enabled', false);

        if (! $enabled) {
            return;
        }

        $ttl = config('whois.cache.ttl', 3600);

        Cache::put(
            $this->cacheKey($domain),
            $result,
            $ttl
        );
    }
}
