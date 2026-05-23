<?php

namespace OTGH\LaravelWhois\Support;

class Retry
{
    public static function run(int $attempts, array $backoff, callable $callback)
    {
        $attempt = 0;

        while (true) {
            try {
                return $callback();
            } catch (\Throwable $e) {
                $attempt++;

                if ($attempt >= $attempts) {
                    throw $e;
                }
                $delay = $backoff[$attempt - 1] ?? end($backoff);

                sleep($delay);
            }
        }
    }
}
