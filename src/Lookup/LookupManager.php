<?php

namespace OTGH\LaravelWhois\Lookup;

use OTGH\LaravelWhois\Domain\DomainParser;
use OTGH\LaravelWhois\Lookup\Strategy\RdapStrategy;
use OTGH\LaravelWhois\Lookup\Strategy\WhoisStrategy;
use OTGH\LaravelWhois\Parsers\ParsedResult;
use OTGH\LaravelWhois\Support\CachesWhois;

class LookupManager
{
    use CachesWhois;

    public bool $ignoreCache = false;

    protected string $domain;

    protected array $sources;

    public function __construct(string $domain, array $sources = [])
    {
        $this->domain = $domain;

        $this->sources = ! empty($sources)
            ? $sources
            : config('whois.default_sources', ['rdap', 'whois']);
    }

    public function withoutCache(): self
    {
        $this->ignoreCache = true;

        return $this;
    }

    public function run(): ParsedResult
    {
        $parsed = DomainParser::parse($this->domain);
        $mode = config('whois.lookup_mode', 'safe'); // 'safe' or 'strict'

        if (
            ! $this->ignoreCache &&
            ($cached = $this->getCached($parsed->registrable))
        ) {
            $cached->servedFromCache = true;

            return $cached;
        }

        $result = new ParsedResult;

        foreach ($this->sources as $source) {

            try {
                if ($source === 'rdap') {
                    $result = (new RdapStrategy)->lookup($parsed);
                }

                if ($source === 'whois') {
                    $result = (new WhoisStrategy)->lookup($parsed);
                }

                if (! $this->ignoreCache) {
                    $this->putCached($parsed->registrable, $result);
                }

                return $result;

            } catch (\Throwable $e) {

                if ($mode === 'strict') {
                    throw $e;
                }

                $result->errors[] = [
                    'source' => $source,
                    'exception' => class_basename($e),
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toISOString(),
                ];
            }
        }

        return $result;
    }
}
