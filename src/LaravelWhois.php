<?php

namespace OTGH\LaravelWhois;

use OTGH\LaravelWhois\Lookup\LookupManager;

class LaravelWhois
{
    public static function lookup(string $domain, array $options = [])
    {
        $sources = $options['sources']
            ?? config('whois.default_sources', ['rdap', 'whois']);

        $ignoreCache = $options['ignoreCache'] ?? false;

        $manager = new LookupManager($domain, $sources);

        if ($ignoreCache) {
            $manager->withoutCache();
        }

        return $manager->run();
    }
}
