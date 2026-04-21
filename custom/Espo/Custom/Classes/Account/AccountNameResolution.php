<?php

namespace Espo\Custom\Classes\Account;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * Resolves client display names from the Account link when policy/renewal
 * accountName is missing or a common AMS/import placeholder.
 */
class AccountNameResolution
{
    public static function isPlaceholder(string $name): bool
    {
        $n = strtolower(trim($name));
        if ($n === '') {
            return true;
        }

        $exact = [
            'unknown',
            'unknown client',
            'n/a',
            'na',
            'tbd',
            'tba',
            'client',
            'new client',
            'none',
            'unassigned',
        ];

        if (in_array($n, $exact, true)) {
            return true;
        }

        return str_starts_with($n, 'unknown');
    }

    public static function resolveForPolicy(EntityManager $entityManager, Entity $policy): string
    {
        $raw = trim((string) ($policy->get('accountName') ?? ''));
        $accountId = $policy->get('accountId');
        if (!$accountId) {
            return $raw;
        }

        if ($raw !== '' && !self::isPlaceholder($raw)) {
            return $raw;
        }

        $fromAccount = self::nameFromAccountId($entityManager, (string) $accountId);

        return $fromAccount !== '' ? $fromAccount : $raw;
    }

    public static function resolveForRenewal(EntityManager $entityManager, Entity $renewal): string
    {
        $raw = trim((string) ($renewal->get('accountName') ?? ''));
        $accountId = $renewal->get('accountId');
        if (!$accountId) {
            return $raw;
        }

        if ($raw !== '' && !self::isPlaceholder($raw)) {
            return $raw;
        }

        $fromAccount = self::nameFromAccountId($entityManager, (string) $accountId);

        return $fromAccount !== '' ? $fromAccount : $raw;
    }

    private static function nameFromAccountId(EntityManager $entityManager, string $accountId): string
    {
        $account = $entityManager->getEntityById('Account', $accountId);
        if (!$account) {
            return '';
        }

        return trim((string) ($account->get('name') ?? ''));
    }
}
