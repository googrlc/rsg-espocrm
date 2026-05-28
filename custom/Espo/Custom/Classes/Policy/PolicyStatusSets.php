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
