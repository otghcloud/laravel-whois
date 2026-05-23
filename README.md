[<img src="https://otgh-static-assets.s3.otgh.cloud/branding/logos/otgh_cloud_2024.png" width="200px" />](https://git.otgh.cloud/open-source/laravel/whois)

[![pipeline status](https://git.otgh.cloud/open-source/laravel/whois/badges/main/pipeline.svg)](https://git.otgh.cloud/open-source/laravel/whois/-/commits/main)
[![Latest Release](https://git.otgh.cloud/open-source/laravel/whois/-/badges/release.svg)](https://git.otgh.cloud/open-source/laravel/whois/-/releases)

# Laravel RDAP/WHOIS Domain Lookups

A lightweight, all-in-one RDAP/WHOIS domain lookup tool built for Laravel.

Inspired by the excellent [spatie/laravel-rdap](https://github.com/spatie/laravel-rdap) package, our internal use case required legacy WHOIS support as many TLD's have not yet made the switch to RDAP.

## Features

- **Built-in Caching** - Results are intelligently cached to avoid lookup throttling
- **Comprehensive Data Parsing** - Extract registrar, dates, nameservers, and more
- **Multiple Data Sources** - Support for both WHOIS and RDAP protocols


## Installation

You can install the package via composer:

```bash
composer require otghcloud/laravel-whois
```

## Configuration

If you wish to modify default lookup timeouts and caching behaviour, you can publish the [config file](config/whois.php) by running the below:

```bash
php artisan vendor:publish --tag="laravel-whois-config"
```

This creates `config/whois.php`, where you can control cache behavior, timeouts, retries, strictness, and source preference.

```php
return [
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
    'timeout_whois' => 15,
    'timeout_rdap' => 15,
    'retry_attempts' => 3,
    'retry_backoff' => [2, 10, 30],
    'lookup_mode' => 'safe', // 'safe' or 'strict'
    'default_sources' => ['rdap', 'whois'],
];
```

Available options:

- `cache.enabled`: Enables/disables response caching.
- `cache.ttl`: Cache duration in seconds.
- `timeout_whois`: WHOIS request timeout in seconds.
- `timeout_rdap`: RDAP request timeout in seconds.
- `retry_attempts`: Number of retry attempts for transient failures.
- `retry_backoff`: Backoff schedule (in seconds) applied across retries.
- `lookup_mode`: `safe` returns a parsed result with error metadata, `strict` throws exceptions.
- `default_sources`: Default lookup order, e.g. `['rdap', 'whois']`.

## Usage

The simplest way to get started is to use our global "whois" function:

```php
$result = whois('example.com');

= OTGH\LaravelWhois\Parsers\ParsedResult {#9061
    +domain: "example.com",
    +registered: true,
    +registrar: "RESERVED-Internet Assigned Numbers Authority",
    +registrarURL: null,
    +createdAt: Carbon\Carbon @808372800 {#9049
      date: 1995-08-14 04:00:00.0 +00:00,
    },
    +updatedAt: Carbon\Carbon @1768588010 {#9060
      date: 2026-01-16 18:26:50.0 +00:00,
    },
    +expiresAt: Carbon\Carbon @1786593600 {#9058
      date: 2026-08-13 04:00:00.0 +00:00,
    },
    +status: [
      "client delete prohibited",
      "client transfer prohibited",
      "client update prohibited",
    ],
    +nameServers: [
      "elliott.ns.cloudflare.com",
      "hera.ns.cloudflare.com",
    ],
    +ageSeconds: 0,
    +remainingSeconds: 0,
    +grace: false,
    +redemption: false,
    +pendingDelete: false,
    +unknown: [],
    +rawResponse: "", // Ommited for brevity - returns the raw RDAP/WHOIS data in JSON format
    +source: "rdap",
    +servedFromCache: true,
    +errors: [],
  }

// By default, results are cached after the first lookup.
// To bypass cache, pass ignoreCache in the options array:

$result = whois('example.com', ['ignoreCache' => true]);

```

Alternatively, you can include the Facade:

```php

use OTGH\LaravelWhois\Facades\LaravelWhois;

$result = LaravelWhois::lookup('example.com');
dump($result);

// Same as above, bypassing the cache
$freshResult = LaravelWhois::lookup('example.com', ['ignoreCache' => true]);

echo $result->domain;
echo $result->registrar;
echo $result->expiresAt?->toDateString();

// Convenience methods
$result->fetchDates();
$result->isExpired();
$result->isRegistered();
$result->daysUntilExpiry();
$result->toArray();
$result->toJson();
```

## Credits

- [jeremykendall/php-domain-parser](https://github.com/jeremykendall/php-domain-parser) - An amazing domain parsing package; used to determine the TLD/gTLD of a domain so we can select the correct RDAP/WHOIS endpoint.
- [spatie/laravel-rdap](https://github.com/spatie/laravel-rdap) - Alternative RDAP only lookup package for Laravel.

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
