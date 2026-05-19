<?php

namespace Espo\Custom\Classes\Account;

use DateTimeImmutable;

trait AccountDataTrait
{
    private function normalizeMultiEnum(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $value
        )));
    }

    private function extractCarrierValues(string $value): array
    {
        $parts = preg_split('/[;,|]+/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    private function toDate(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        return new DateTimeImmutable(substr(str_replace('T', ' ', $value), 0, 10));
    }
}
