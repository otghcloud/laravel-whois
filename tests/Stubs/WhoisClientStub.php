<?php

namespace Tests\Stubs;

class WhoisClientStub
{
    public function __construct(
        public string $response
    ) {}

    public function getData(): string
    {
        return $this->response;
    }
}
