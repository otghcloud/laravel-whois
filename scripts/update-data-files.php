#!/usr/bin/env php
<?php

/**
 * OTGH Laravel-Whois Data Updater
 *
 * Fetches authoritative upstream data files and overwrites copies inside src/Data.
 * Intended to be run manually or via CI before tagging a release.
 */

declare(strict_types=1);

$baseDir = dirname(__DIR__).'/src/Data/';
$dryRun = in_array('--dry-run', $argv, true);

$files = [
    'public-suffix-list.dat' => 'https://publicsuffix.org/list/public_suffix_list.dat',
    'rdap-servers-iana.json' => 'https://data.iana.org/rdap/dns.json',
    'whois-servers-iana.json' => 'https://whoislist.org/whois_servers.json',
];

echo "=== OTGH Laravel-Whois Data Updater ===\n\n";

if ($dryRun) {
    echo "Dry run: no files will be written.\n\n";
}

$failed = false;
$updated = 0;

foreach ($files as $filename => $url) {
    echo "Fetching: $filename\n";
    echo "Source:   $url\n";

    $target = $baseDir.$filename;

    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 20,
                'user_agent' => 'Laravel-Whois Updater',
            ],
        ]);

        $data = @file_get_contents($url, false, $context);

        if ($data === false) {
            throw new RuntimeException("Failed to fetch URL: $url");
        }

        $hasChanged = true;

        if (file_exists($target)) {
            $existing = file_get_contents($target);
            if ($existing === $data) {
                $hasChanged = false;
            }
        }

        if ($hasChanged) {
            if ($dryRun) {
                echo "Would update: $filename\n";
            } else {
                if (file_put_contents($target, $data) === false) {
                    throw new RuntimeException("Failed to write file: $target");
                }

                echo "Updated: $filename\n";
            }

            $updated++;
        } else {
            echo "No changes: $filename (skipped)\n";
        }

    } catch (Throwable $e) {
        echo "Error updating $filename: ".$e->getMessage()."\n";
        $failed = true;
    }

    echo "\n";
}

if ($failed) {
    echo "Data update failed.\n\n";
    exit(1);
}

echo $updated === 0 ? "Data files are already current.\n\n" : "Data update complete.\n\n";
