<?php

namespace OTGH\LaravelWhois\Clients;

use RuntimeException;

class RdapClient
{
    use RdapClientTrait;

    public string $domain;

    public string $extension;

    public ?string $extensionTop;

    private array $servers = [];

    private string $server = '';

    private const SERVERS_IANA = __DIR__.'/../Data/rdap-servers-iana.json';

    private const SERVERS_EXTRA = __DIR__.'/../Data/rdap-servers-extra.json';

    public function __construct(
        string $domain,
        string $extension,
        ?string $extensionTop = null,
        ?string $overrideServer = null
    ) {
        $this->domain = strtolower($domain);
        $this->extension = strtolower($extension);
        $this->extensionTop = strtolower($extensionTop);

        $this->servers = $this->loadServers();

        if ($overrideServer) {
            $this->server = rtrim($overrideServer, '/').'/';

            return;
        }

        if (isset($this->servers[$this->extension])) {
            $this->server = $this->servers[$this->extension];
        } elseif (! empty($this->extensionTop) && isset($this->servers[$this->extensionTop])) {
            $this->server = $this->servers[$this->extensionTop];
        } else {
            throw new RuntimeException("No RDAP server found for '{$this->domain}'");
        }

        $this->server = rtrim($this->server, '/').'/';
    }

    public function getData(): array
    {
        $url = $this->server.'domain/'.$this->domain;

        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $ctype = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        curl_close($curl);

        if (! preg_match('/json/i', (string) $ctype)) {
            if (json_decode($response, true) === null) {
                $response = '';
            }
        }

        return [$code, (string) $response];
    }
}
