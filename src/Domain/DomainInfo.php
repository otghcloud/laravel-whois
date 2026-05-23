<?php

namespace OTGH\LaravelWhois\Domain;

class DomainInfo
{
    public function __construct(
        public string $registrable,
        public string $extension,
        public ?string $extensionTop,
        public string $inputDomain,
        public ?string $subdomain = null
    ) {}
}
