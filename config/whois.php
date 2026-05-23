<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Enables response caching to dramatically reduce lookup load.
    |
    | enabled:  true/false — enable caching
    | ttl:      Cache TTL in seconds
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,   // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | Define the timeout in seconds for WHOIS and RDAP queries.
    |
    */
    'timeout_whois' => 15,
    'timeout_rdap' => 15,

    /*
    |--------------------------------------------------------------------------
    | Retry Logic
    |--------------------------------------------------------------------------
    |
    | Number of retry attempts for WHOIS/RDAP queries when failures occur.
    | Useful when dealing with rate limits or transient network errors.
    |
    | retry_attempts: how many times to retry
    | retry_backoff: array of seconds defining backoff per attempt
    |                if count(retry_backoff) < retry_attempts,
    |                the last value will be reused.
    |
    */
    'retry_attempts' => 3,
    'retry_backoff' => [2, 10, 30],

    /*
    |--------------------------------------------------------------------------
    | Lookup Error Mode
    |--------------------------------------------------------------------------
    | 'safe'   → never throw; return ParsedResult with error metadata
    | 'strict' → throw exceptions immediately
    */
    'lookup_mode' => 'safe',

    /*
    |--------------------------------------------------------------------------
    | Default Data Sources
    |--------------------------------------------------------------------------
    |
    | Specify the default data sources to use for lookups.
    |
    */
    'default_sources' => ['rdap', 'whois'],

];
