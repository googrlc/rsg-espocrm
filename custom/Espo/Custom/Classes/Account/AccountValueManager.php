<?php

namespace Espo\Custom\Classes\Account;

use DateTimeImmutable;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\Config;
use Espo\Custom\Classes\Policy\PolicyStatusSets;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class AccountValueManager
{
    use AccountDataTrait;

    public const SKIP_VALUE_SNAPSHOT_OPTION = 'skipValueSnapshot';

    private const DEFAULT_DISCOUNT_RATE = 0.10;
    private const DEFAULT_GROSS_MARGIN = 0.80;
    private const DEFAULT_RETENTION_BY_TIER = [
        'Strong' => 0.95,
        'Good' => 0.85,
        'At Risk' => 0.65,
        'Critical' => 0.40,
    ];
    private const DEFAULT_RETENTION_FALLBACK = 0.70;
    private const DEFAULT_CROSS_SELL_UPLIFT_PER_GAP = 500.0;
    private const DEFAULT_CROSS_SELL_TIER_MULTIPLIER = [
        'Strong' => 1.0,
        'Good' => 0.7,
        'At Risk' => 0.3,
        'Critical' => 0.1,
    ];

    private const GAP_FIELDS = [
        'gapUmbrella',
        'gapLife',
        'gapRenters',
        'gapAutoUm',
        'gapMedicare',
        'gapLandlord',
        'gapRideshare',
        'gapFinalExpense',
    ];

    public function __construct(
        private EntityManager $entityManager,
        private Config $config
    ) {}

    public function refreshByAccountId(string $accountId): void
    {
        if ($accountId === '') {
            return;
        }

        $account = $this->entityManager->getEntityById('Account', $accountId);
        if (!$account) {
            return;
        }

        $this->applyToAccount($account);
        // SKIP_HOOKS mirrors AccountHealthManager::refreshByAccountId: the nightly
        // recalc must be a pure computation + persist, not a re-trigger of side-effect
        // hooks (SyncCrossSellPlaybooks creates tasks whose assignedUser validation
        // would abort the whole run on any account owned by a non-Gretchen/Lamar user).
        $this->entityManager->saveEntity($account, [
            SaveOption::SILENT => true,
            SaveOption::SKIP_HOOKS => true,
            self::SKIP_VALUE_SNAPSHOT_OPTION => true,
            \Espo\Custom\Classes\Account\AccountHealthManager::SKIP_HEALTH_SNAPSHOT_OPTION => true,
        ]);
    }

    public function applyToAccount(Entity $account): void
    {
        $accountId = (string) ($account->getId() ?? '');
        $policyList = $accountId !== ''
            ? $this->entityManager->getRDBRepository('Policy')->where(['accountId' => $accountId])->find()
            : [];

        $today = new DateTimeImmutable('today');

        $annualCommission = $this->getAnnualCommission($policyList);
        $tenureYears = $this->getTenureYears($policyList, $today);
        $grossMargin = (float) ($this->config->get('clvGrossMargin') ?? self::DEFAULT_GROSS_MARGIN);
        $discountRate = (float) ($this->config->get('clvDiscountRate') ?? self::DEFAULT_DISCOUNT_RATE);
        $retentionRate = $this->getRetentionRate($account);

        $clvCurrent = $this->computeCurrent($annualCommission, $tenureYears, $grossMargin);
        $clvProjected = $this->computeProjected($annualCommission, $grossMargin, $retentionRate, $discountRate);

        $account->set('clv_annual_commission', round($annualCommission, 2));
        $account->set('clv_tenure_years', round($tenureYears, 2));
        $account->set('clv_retention_rate_applied', round($retentionRate, 4));
        $account->set('clv_current', round($clvCurrent, 2));
        $account->set('clv_projected', round($clvProjected, 2));
        $account->set('clv_last_calculated', gmdate('Y-m-d H:i:s'));
    }

    private function getAnnualCommission(iterable $policyList): float
    {
        $total = 0.0;

        foreach ($policyList as $policy) {
            $status = trim((string) ($policy->get('status') ?? ''));
            if (!in_array($status, PolicyStatusSets::ACTIVE, true)) {
                continue;
            }

            $total += (float) ($policy->get('commissionAmount') ?? 0);
        }

        return $total;
    }

    private function getTenureYears(iterable $policyList, DateTimeImmutable $today): float
    {
        $oldest = null;

        foreach ($policyList as $policy) {
            $status = trim((string) ($policy->get('status') ?? ''));
            if (!in_array($status, PolicyStatusSets::ACTIVE, true)) {
                continue;
            }

            $effectiveDate = $this->toDate((string) ($policy->get('effective_date') ?? ''));
            if ($effectiveDate && ($oldest === null || $effectiveDate < $oldest)) {
                $oldest = $effectiveDate;
            }
        }

        if (!$oldest) {
            return 0.0;
        }

        return max(0.0, (float) $today->diff($oldest)->days / 365.25);
    }

    private function getRetentionRate(Entity $account): float
    {
        $tier = trim((string) ($account->get('score_tier') ?? ''));
        $table = (array) ($this->config->get('clvRetentionByTier') ?? self::DEFAULT_RETENTION_BY_TIER);

        if ($tier !== '' && isset($table[$tier])) {
            return (float) $table[$tier];
        }

        return self::DEFAULT_RETENTION_FALLBACK;
    }

    private function getCrossSellUplift(Entity $account): float
    {
        $gapCount = 0;
        foreach (self::GAP_FIELDS as $field) {
            if ((bool) $account->get($field)) {
                $gapCount++;
            }
        }

        if ($gapCount === 0) {
            return 0.0;
        }

        $upliftPerGap = (float) ($this->config->get('clvCrossSellUpliftPerGap') ?? self::DEFAULT_CROSS_SELL_UPLIFT_PER_GAP);
        $multiplierTable = (array) ($this->config->get('clvCrossSellTierMultiplier') ?? self::DEFAULT_CROSS_SELL_TIER_MULTIPLIER);
        $tier = trim((string) ($account->get('score_tier') ?? ''));
        $multiplier = $tier !== '' && isset($multiplierTable[$tier])
            ? (float) $multiplierTable[$tier]
            : 0.5;

        return max(0.0, $gapCount * $upliftPerGap * $multiplier);
    }

    private function computeCurrent(float $annualCommission, float $tenureYears, float $grossMargin): float
    {
        return max(0.0, $annualCommission * $tenureYears * $grossMargin);
    }

    private function computeProjected(
        float $annualCommission,
        float $grossMargin,
        float $retentionRate,
        float $discountRate
    ): float {
        if ($annualCommission <= 0) {
            return 0.0;
        }

        $denominator = 1.0 + $discountRate - $retentionRate;
        if ($denominator <= 0.0) {
            return 0.0;
        }

        return max(0.0, $annualCommission * $grossMargin * ($retentionRate / $denominator));
    }
}
