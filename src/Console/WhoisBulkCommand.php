<?php

namespace OTGH\LaravelWhois\Console;

use Illuminate\Console\Command;
use OTGH\LaravelWhois\LaravelWhois;

class WhoisBulkCommand extends Command
{
    protected $signature = 'whois:bulk
        {file : Path to file containing one domain per line}
        {--no-cache : Force fresh lookup}
        {--json : Output JSON array}';

    protected $description = 'Perform bulk WHOIS/RDAP lookups from a file';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return static::FAILURE;
        }

        $domains = array_filter(array_map('trim', file($path)));
        $results = [];

        foreach ($domains as $domain) {
            $result = LaravelWhois::lookup($domain, ['ignoreCache' => $this->option('no-cache')]);
            $results[$domain] = $result->toArray();

            $this->info("{$domain}: ".($result->registered ? 'registered' : 'free'));
        }

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
        }

        return static::SUCCESS;
    }
}
