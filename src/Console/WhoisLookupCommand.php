<?php

namespace OTGH\LaravelWhois\Console;

use Illuminate\Console\Command;
use OTGH\LaravelWhois\LaravelWhois;

class WhoisLookupCommand extends Command
{
    protected $signature = 'whois:lookup 
        {domain : The domain name to look up}
        {--no-cache : Ignore cache and force fresh lookup}
        {--json : Output result as JSON}';

    protected $description = 'Perform a RDAP/WHOIS lookup for a domain';

    public function handle(): int
    {
        $domain = $this->argument('domain');
        $ignoreCache = $this->option('no-cache');

        $result = LaravelWhois::lookup($domain, ignoreCache: $ignoreCache);

        if ($this->option('json')) {
            $this->line($result->toJson(JSON_PRETTY_PRINT));

            return static::SUCCESS;
        }

        $this->info("Domain:        {$result->domain}");
        $this->info('Registered:    '.($result->registered ? 'Yes' : 'No'));
        $this->info("Source:        {$result->source}");
        $this->info("Registrar:     {$result->registrar}");
        $this->info("Registrar URL: {$result->registrarURL}");
        $this->info('Created:       '.optional($result->createdAt)->toDateTimeString());
        $this->info('Updated:       '.optional($result->updatedAt)->toDateTimeString());
        $this->info('Expires:       '.optional($result->expiresAt)->toDateTimeString());
        $this->info('From Cache:    '.($result->servedFromCache ? 'Yes' : 'No'));

        if (! empty($result->errors)) {
            $this->warn('Errors:');
            foreach ($result->errors as $err) {
                $this->warn(" - {$err['exception']}: {$err['message']}");
            }
        }

        return static::SUCCESS;
    }
}
