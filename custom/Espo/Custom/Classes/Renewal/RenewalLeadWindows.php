<?php

namespace Espo\Custom\Classes\Renewal;

use Espo\ORM\Entity;

/**
 * Days-before-expiration when the renewal pipeline opens, by line of business.
 * Commercial P&C: 90 days. Personal P&C: 30 days. Other LOBs default to 90.
 */
class RenewalLeadWindows
{
    public const COMMERCIAL_DAYS = 90;

    public const PERSONAL_DAYS = 30;

    public const DEFAULT_DAYS = 90;

    /** @var list<string> */
    private const COMMERCIAL_LOBS = [
        'Commercial Auto',
        'General Liability',
        'Workers Comp',
        'Commercial Property',
        'BOP',
        'Professional Liability',
        'Umbrella',
        'Builders Risk',
        'Inland Marine',
        'Garagekeepers',
        'Commercial Package',
    ];

    /** @var list<string> */
    private const PERSONAL_LOBS = [
        'Personal Auto',
        'Homeowners',
        'Renters',
        'Condo',
        'Dwelling Fire',
        'Motorcycle',
        'Boat',
        'RV',
    ];

    public static function normalizeLobPart(mixed $value): string
    {
        $line = trim((string) $value);

        return match ($line) {
            'GL' => 'General Liability',
            'Auto' => 'Personal Auto',
            'Home' => 'Homeowners',
            default => $line,
        };
    }

    /**
     * If a policy lists multiple LOBs (comma-separated), use the largest lead window.
     */
    public static function leadDaysForPolicy(Entity $policy): int
    {
        $raw = trim((string) ($policy->get('lineOfBusiness') ?? $policy->get('businessType') ?? ''));
        if ($raw === '') {
            return self::DEFAULT_DAYS;
        }

        $parts = preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === []) {
            return self::DEFAULT_DAYS;
        }

        $maxDays = self::PERSONAL_DAYS;
        foreach ($parts as $part) {
            $maxDays = max($maxDays, self::leadDaysForNormalizedLob(self::normalizeLobPart($part)));
        }

        return $maxDays;
    }

    public static function leadDaysForNormalizedLob(string $normalizedLob): int
    {
        if ($normalizedLob === '') {
            return self::DEFAULT_DAYS;
        }

        if (in_array($normalizedLob, self::COMMERCIAL_LOBS, true)) {
            return self::COMMERCIAL_DAYS;
        }

        if (in_array($normalizedLob, self::PERSONAL_LOBS, true)) {
            return self::PERSONAL_DAYS;
        }

        return self::DEFAULT_DAYS;
    }
}
