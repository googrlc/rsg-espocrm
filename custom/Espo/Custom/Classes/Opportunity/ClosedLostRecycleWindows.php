<?php

namespace Espo\Custom\Classes\Opportunity;

use DateInterval;
use DateTimeImmutable;

class ClosedLostRecycleWindows
{
    public const COMMERCIAL_DAYS = 90;

    public const PERSONAL_DAYS = 30;

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
        'Commercial Package',
        'Garagekeepers',
        'Group Benefits',
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
        'Life',
        'Health',
        'Medicare',
    ];

    public static function callbackDaysForLob(?string $lineOfBusiness): int
    {
        $lob = trim((string) $lineOfBusiness);

        if (in_array($lob, self::COMMERCIAL_LOBS, true)) {
            return self::COMMERCIAL_DAYS;
        }

        if (in_array($lob, self::PERSONAL_LOBS, true)) {
            return self::PERSONAL_DAYS;
        }

        return self::PERSONAL_DAYS;
    }

    public static function callbackDateFromXDate(?string $xDate, ?string $lineOfBusiness): ?string
    {
        $value = trim((string) $xDate);
        if ($value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if (!$date) {
            return null;
        }

        $days = self::callbackDaysForLob($lineOfBusiness);
        return $date->sub(new DateInterval(sprintf('P%dD', $days)))->format('Y-m-d');
    }
}
