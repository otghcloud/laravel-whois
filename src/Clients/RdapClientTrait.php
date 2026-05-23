<?php

namespace OTGH\LaravelWhois\Clients;

trait RdapClientTrait
{
    public function loadServers(): array
    {
        $servers = [];

        if (file_exists(static::SERVERS_IANA) &&
            ($json = file_get_contents(static::SERVERS_IANA)) !== false) {

            $decoded = json_decode($json, true);

            if (is_array($decoded) && isset($decoded['services'])) {
                foreach ($decoded['services'] as $service) {
                    $tlds = $service[0];
                    $server = rtrim($service[1][0] ?? '', '/').'/';

                    foreach ($tlds as $tld) {
                        $servers[strtolower($tld)] = $server;
                    }
                }
            }
        }

        if (file_exists(static::SERVERS_EXTRA) &&
            ($json = file_get_contents(static::SERVERS_EXTRA)) !== false) {

            $decoded = json_decode($json, true);

            if (is_array($decoded)) {
                foreach ($decoded as $tld => $url) {
                    $servers[strtolower($tld)] = rtrim($url, '/').'/';
                }
            }
        }

        return $servers;
    }

    public static function servers(): array
    {
        $dummy = new class
        {
            use RdapClientTrait;

            private const SERVERS_IANA = __DIR__.'/../Data/rdap-servers-iana.json';

            private const SERVERS_EXTRA = __DIR__.'/../Data/rdap-servers-extra.json';
        };

        return $dummy->loadServers();
    }
}
