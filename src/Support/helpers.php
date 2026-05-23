<?php

use OTGH\LaravelWhois\LaravelWhois;
use OTGH\LaravelWhois\Parsers\ParsedResult;

if (! function_exists('whois')) {

    /**
     * Perform a LaravelWhois lookup.
     *
     * Example:
     *   whois('example.com');
     *   whois('example.com', ignoreCache: true);
     *
     * @return ParsedResult
     */
    function whois(string $domain, bool $ignoreCache = false, array $options = [])
    {
        return (new LaravelWhois)->lookup($domain, $options, $ignoreCache);
    }
}
