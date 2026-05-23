<?php

namespace OTGH\LaravelWhois\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use OTGH\LaravelWhois\Support\CachesWhois;

class WhoisCacheClearCommand extends Command
{
    use CachesWhois;

    protected $signature = 'whois:cache:clear
        {domain : Domain name whose cache should be cleared}';

    protected $description = 'Clear cached WHOIS/RDAP data for a domain';

    public function handle(): int
    {
        $domain = strtolower($this->argument('domain'));
        Cache::forget($this->cacheKey($domain));

        $this->info("Cache cleared for {$domain}");

        return static::SUCCESS;
    }
}
