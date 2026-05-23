[<img src="https://otgh-static-assets.s3.otgh.cloud/branding/logos/otgh_cloud_2024.png" width="200px" />](https://git.otgh.cloud/open-source/laravel/whois)

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

By default, we reference RDAP/WHOIS servers defined in the package (located in src/Data/); we update these lists when building each release.

If you wish to customize the these, you can publish this packages config file and amend them accordingly.

```bash
php artisan vendor:publish --tag="laravel-whois-config"
```

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Data Paths
    |--------------------------------------------------------------------------
    |
    | Allows overriding the default paths for data files. By default,
    | it uses the files within the package. You can publish and
    | customize these files if needed.
    |
    */
    'paths' => [
        'public_suffix_list' => null,
        'rdap_servers_iana' => null,
        'rdap_servers_extra' => null,
        'whois_servers_iana' => null,
        'whois_servers_extra' => null,
    ],
];
```

## Usage

```php
// Include the Facade
use OTGH\LaravelWhois\Facades\LaravelWhois;

// Call the Parser
$result = LaravelWhois::lookup('otgh.cloud');
dump($result);

// You can also retrieve individual properties
echo $result->domain;
echo $result->registrar;
echo $result->expiresAt?->toDateString();

// Helper methods

$result->fetchDates();
$result->isExpired();
$result->isRegistered();
$result->daysUntilExpiry();
$result->toArray();
$result->toJson();


```

## Testing

```bash
composer test
```

## Credits

- [jeremykendall/php-domain-parser](https://github.com/jeremykendall/php-domain-parser) - An amazing domain parsing package; used to determine the TLD/gTLD of a domain so we can select the correct RDAP/WHOIS endpoint.
- [spatie/laravel-rdap](https://github.com/spatie/laravel-rdap) - Alternative RDAP only lookup package for Laravel.

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
