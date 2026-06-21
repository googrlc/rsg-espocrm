<?php

namespace Espo\Custom\Classes\Policy;

final class PolicyStatusSets
{
    public const ACTIVE = [
        'Active',
        'Up for Renewal',
        'Renewing',
        'Renewed',
    ];

    /**
     * Active-coverage statuses that should lapse to "Expired" once the term ends.
     * Excludes 'Renewed' — a completed renewal must never be relabelled as lapsed.
     */
    public const LAPSE_ON_EXPIRY = [
        'Active',
        'Up for Renewal',
        'Renewing',
    ];

    public const RENEWING = [
        'Up for Renewal',
        'Renewing',
    ];

    public const TERMINAL = [
        'Expired',
        'Cancelled',
        'Flat Cancel',
        'Pending Cancel',
        'Non-Renewed',
        'Lapsed',
    ];

    private function __construct() {}
}
