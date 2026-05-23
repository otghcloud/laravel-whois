<?php

namespace OTGH\LaravelWhois\Domain;

use Pdp\Domain;
use Pdp\Rules;
use Throwable;

class DomainParser
{
    public static function parse(string $input): DomainInfo
    {
        $publicSuffixList = Rules::fromPath(__DIR__.'/../Data/public-suffix-list.dat');
        $domainObj = Domain::fromIDNA2008($input);

        try {
            $domainName = $publicSuffixList->getPrivateDomain($domainObj);
        } catch (Throwable) {
            try {
                $domainName = $publicSuffixList->getICANNDomain($domainObj);
            } catch (Throwable) {
                return new DomainInfo(
                    registrable: $domainObj->toString(),
                    extension: 'iana',
                    extensionTop: null,
                    inputDomain: $input,
                    subdomain: null
                );
            }
        }

        $registrable = strtolower($domainName->registrableDomain()->toString());
        $suffix = strtolower($domainName->suffix()->toString());

        $labels = explode('.', $suffix);
        $tld = end($labels);

        // compute subdomain if present
        $inputLabels = explode('.', strtolower($input));
        $subdomain = count($inputLabels) > (count(explode('.', $registrable)))
            ? implode('.', array_slice($inputLabels, 0, -count(explode('.', $registrable))))
            : null;

        return new DomainInfo(
            registrable: $registrable,
            extension: $suffix,
            extensionTop: $tld,
            inputDomain: $input,
            subdomain: $subdomain
        );
    }
}
