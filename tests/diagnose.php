#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use OTGH\LaravelWhois\Clients\RdapClient;
use OTGH\LaravelWhois\Clients\WhoisClient;
use OTGH\LaravelWhois\Domain\DomainParser;

// ---------------------------------------------
// INPUT
// ---------------------------------------------

if ($argc < 2) {
    echo "Usage: php diagnose.php <domain>\n";
    exit(1);
}

$domain = trim($argv[1]);

echo "\n=== LaravelWhois Diagnostics ===\n";
echo "Input: $domain\n";

// ---------------------------------------------
// PARSE DOMAIN
// ---------------------------------------------

$parsed = DomainParser::parse($domain);

echo "\nParsed Domain Components:\n";
echo "- registrable:   {$parsed->registrable}\n";
echo "- extension:     {$parsed->extension}\n";
echo "- extensionTop:  {$parsed->extensionTop}\n";
echo '- subdomain:     '.($parsed->subdomain ?: '(none)')."\n";
echo "- inputDomain:   {$parsed->inputDomain}\n";

// ---------------------------------------------
// RDAP
// ---------------------------------------------

$servers = RdapClient::servers();
$ext = strtolower($parsed->extension);
$extTop = strtolower($parsed->extensionTop);

$rdapSupported = isset($servers[$ext]) || isset($servers[$extTop]);

echo "\nRDAP Server Detection:\n";
echo '- supported: '.($rdapSupported ? 'YES' : 'NO')."\n";
echo '- server:    '.($servers[$ext] ?? $servers[$extTop] ?? '(none)')."\n";

// ---------------------------------------------
// WHOIS
// ---------------------------------------------

echo "\nWHOIS Server Detection:\n";

try {
    $whois = new WhoisClient(
        $parsed->registrable,
        $parsed->extension,
        $parsed->extensionTop
    );
    $server = $whois->getServer();

    echo '- server:    '.(is_string($server) ? $server : json_encode($server))."\n";

} catch (Throwable $e) {
    echo "- WHOIS error: {$e->getMessage()}\n";
}

// ---------------------------------------------
// STRATEGY
// ---------------------------------------------

echo "\nStrategy Plan:\n";

if ($rdapSupported) {
    echo "→ RDAP FIRST\n";
    echo "→ WHOIS fallback\n";
} else {
    echo "→ WHOIS ONLY\n";
}

echo "\n=== End Diagnostics ===\n\n";
