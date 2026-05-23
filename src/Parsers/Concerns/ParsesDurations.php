<?php

namespace OTGH\LaravelWhois\Parsers\Concerns;

trait ParsesDurations
{
    protected function computeDurations(): void
    {
        $now = time();

        if ($this->result->createdAt) {
            $this->result->ageSeconds = max(0, $now - $this->result->createdAt->timestamp);
        }

        if ($this->result->expiresAt) {
            $this->result->remainingSeconds = max(0, $this->result->expiresAt->timestamp - $now);
        }
    }
}
