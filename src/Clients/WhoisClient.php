<?php

namespace OTGH\LaravelWhois\Clients;

use RuntimeException;

class WhoisClient
{
    public string $domain;

    public string $extension;

    private array $servers = [];

    /**
     * WHOIS server may be:
     * - a string: "whois.verisign-grs.com"
     * - an array: ["host" => "...", "query" => "..."]
     */
    private string|array $server = '';

    private ?string $extensionTop;

    private const SERVERS_IANA = __DIR__.'/../Data/whois-servers-iana.json';

    private const SERVERS_EXTRA = __DIR__.'/../Data/whois-servers-extra.json';

    public function __construct(string $domain, string $extension, ?string $extensionTop = null, ?string $overrideServer = null)
    {
        $this->domain = $domain;
        $this->extension = $extension;
        $this->extensionTop = $extensionTop;

        $this->servers = $this->getServers();

        // Fall back to top-level extension if needed
        if (! empty($extensionTop) && ! array_key_exists($extension, $this->servers)) {
            $this->extension = $extensionTop;
        }

        // Allow overrides
        if ($overrideServer) {
            $this->server = $overrideServer;
        } else {
            $this->server = $this->resolveServer();
        }
    }

    /**
     * Load WHOIS servers from IANA + extra list
     */
    private function getServers(): array
    {
        $servers = [];

        if (file_exists(self::SERVERS_IANA) && ($json = file_get_contents(self::SERVERS_IANA)) !== false) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $servers = array_merge($servers, $decoded);
            }
        }

        if (file_exists(self::SERVERS_EXTRA) && ($json = file_get_contents(self::SERVERS_EXTRA)) !== false) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $servers = array_merge($servers, $decoded);
            }
        }

        return $servers;
    }

    /**
     * Resolve WHOIS server for given TLD
     */
    private function resolveServer(): string|array
    {
        if ($this->extension === 'iana') {
            return 'whois.iana.org';
        }

        $extAscii = idn_to_ascii($this->extension);
        $server = $this->servers[$extAscii] ?? '';

        if (empty($server)) {
            throw new RuntimeException("No WHOIS server found for '{$this->domain}'");
        }

        return $server;
    }

    /**
     * Public getter for the WHOIS server (fixes diagnose.php)
     */
    public function getServer(): string|array|null
    {
        return $this->server;
    }

    /**
     * Perform WHOIS query
     */
    public function getData(): string
    {
        $domain = idn_to_ascii($this->domain);
        $host = $this->server;
        $query = "$domain\r\n";

        // Handle structured server definitions
        if (is_array($this->server)) {
            $host = $this->server['host'];
            $query = str_replace('{domain}', $domain, $this->server['query']);
        }

        $socket = @stream_socket_client("tcp://$host:43", $errno, $errstr, 10);

        if (! $socket) {
            throw new RuntimeException($errstr);
        }

        stream_set_timeout($socket, 10);
        fwrite($socket, $query);

        $data = stream_get_contents($socket);

        // Normalise encoding
        $encoding = mb_detect_encoding($data, ['UTF-8', 'ISO-8859-1'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $data = mb_convert_encoding($data, 'UTF-8', $encoding);
        }

        $meta = stream_get_meta_data($socket);
        fclose($socket);

        if ($meta['timed_out']) {
            throw new RuntimeException('Operation timed out');
        }

        return $data;
    }
}
