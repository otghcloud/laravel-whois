<?php

namespace OTGH\LaravelWhois\Parsers;

use OTGH\LaravelWhois\Parsers\Concerns\ParsesDates;
use OTGH\LaravelWhois\Parsers\Concerns\ParsesDomain;
use OTGH\LaravelWhois\Parsers\Concerns\ParsesNameServers;
use OTGH\LaravelWhois\Parsers\Concerns\ParsesRegistrar;
use OTGH\LaravelWhois\Parsers\Concerns\ParsesStatuses;

class RDAPParser extends BaseParser
{
    use ParsesDates;
    use ParsesDomain;
    use ParsesNameServers;
    use ParsesRegistrar;
    use ParsesStatuses;

    protected function parse(): void
    {
        $json = $this->json;
        $r = $this->result;

        $this->parseDomainFromRdap($json, $r);
        $this->parseRegistrarFromRdap($json, $r);
        $this->parseDatesFromRdap($json, $r);

        $statuses = $json['status'] ?? [];

        if (! is_array($statuses)) {
            $statuses = $statuses ? [$statuses] : [];
        }

        $this->parseStatus($statuses, $r);

        $this->parseNameserversFromRdap($json, $r);
    }
}
