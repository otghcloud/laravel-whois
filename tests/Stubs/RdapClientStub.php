<?php

namespace Tests\Stubs;

class RdapClientStub
{
    public function __construct(
        public string $domain,
        public string $extension,
        public ?string $extensionTop = null,
        public ?string $overrideServer = null,
        private int $code = 200,
        private string $raw = ''
    ) {}

    public function getData(): array
    {
        return [$this->code, $this->raw];
    }
}
