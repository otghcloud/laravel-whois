<?php

namespace OTGH\LaravelWhois\Parsers;

abstract class BaseParser
{
    protected ParsedResult $result;

    public function __construct(
        public ?string $raw = null,
        public ?array $json = null,
        public ?int $code = null
    ) {
        $this->result = new ParsedResult(
            rawResponse: $this->raw ?? ''
        );

        $this->parse();
    }

    abstract protected function parse(): void;

    public function get(): ParsedResult
    {
        return $this->result;
    }

    protected function extractLifecycleFlags(): void
    {
        $r = $this->result;

        $r->grace = false;
        $r->redemption = false;
        $r->pendingDelete = false;

        foreach ($r->status as $status) {
            $lower = strtolower($status);

            if (str_contains($lower, 'grace')) {
                $r->grace = true;
            }
            if (str_contains($lower, 'redemption')) {
                $r->redemption = true;
            }
            if (str_contains($lower, 'pendingdelete') || str_contains($lower, 'pending delete')) {
                $r->pendingDelete = true;
            }
        }
    }

    protected function computeDurations(): void
    {
        $r = $this->result;

        if ($r->createdAt) {
            $r->ageSeconds = now()->diffInSeconds($r->createdAt, false);
        }

        if ($r->expiresAt) {
            $r->remainingSeconds = now()->diffInSeconds($r->expiresAt, false);
        }
    }
}
