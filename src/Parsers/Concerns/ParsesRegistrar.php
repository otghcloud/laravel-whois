<?php

namespace OTGH\LaravelWhois\Parsers\Concerns;

use OTGH\LaravelWhois\Parsers\ParsedResult;

trait ParsesRegistrar
{
    protected function parseRegistrarFromRdap(array $json, ParsedResult $r): void
    {
        if (empty($json['entities'])) {
            return;
        }

        foreach ($json['entities'] as $entity) {
            $roles = array_map('strtolower', $entity['roles'] ?? []);

            if (! in_array('registrar', $roles)) {
                continue;
            }

            if (! empty($entity['vcardArray'][1])) {
                foreach ($entity['vcardArray'][1] as $field) {
                    $key = strtolower($field[0]);

                    if ($key === 'fn') {
                        $r->registrar = $field[3] ?? $r->registrar;
                    }

                    if ($key === 'org' && empty($r->registrar)) {
                        $r->registrar = $field[3] ?? '';
                    }

                    if ($key === 'url') {
                        $r->registrarURL = $field[3] ?? $r->registrarURL;
                    }

                    if ($key === 'contact-uri' && empty($r->registrarURL)) {
                        $r->registrarURL = $field[3] ?? null;
                    }
                }
            }
        }
    }

    protected function parseRegistrarFromWhois(string $raw, ParsedResult $r): void
    {
        if (preg_match('/Registrar:\s*(.+)/i', $raw, $m)) {
            $r->registrar = trim($m[1]);
        }

        if (preg_match('/Registrar URL:\s*(.+)/i', $raw, $m)) {
            $r->registrarURL = trim($m[1]);
        }
    }
}
