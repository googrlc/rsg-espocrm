<?php
namespace Espo\Custom\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\ORM\EntityManager;

class RecalculateAccountScores implements JobDataLess
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function run(): void
    {
        $pdo = $this->entityManager->getPDO();

        $accounts = $pdo->query("SELECT id FROM account WHERE deleted = 0")->fetchAll(\PDO::FETCH_COLUMN);

        $updateStmt = $pdo->prepare(
            "UPDATE account SET account_score = ?, score_breakdown = ?, modified_at = NOW() WHERE id = ?"
        );

        foreach ($accounts as $accountId) {
            $row = $pdo->prepare("SELECT COUNT(*) FROM policy WHERE account_id = ? AND deleted = 0");
            $row->execute([$accountId]);
            $policyCount = (int)$row->fetchColumn();

            $row = $pdo->prepare("SELECT COALESCE(SUM(premium_amount), 0) FROM policy WHERE account_id = ? AND deleted = 0");
            $row->execute([$accountId]);
            $totalPremium = (float)$row->fetchColumn();

            $row = $pdo->prepare("SELECT COUNT(DISTINCT line_of_business) FROM policy WHERE account_id = ? AND deleted = 0 AND line_of_business IS NOT NULL AND line_of_business != ''");
            $row->execute([$accountId]);
            $lobCount = (int)$row->fetchColumn();

            $row = $pdo->prepare("SELECT MIN(effective_date) FROM policy WHERE account_id = ? AND deleted = 0 AND effective_date IS NOT NULL");
            $row->execute([$accountId]);
            $earliest = $row->fetchColumn();
            $tenureYears = 0;
            if ($earliest) {
                $tenureYears = max(0, (time() - strtotime($earliest)) / (365.25 * 86400));
            }

            $row = $pdo->prepare("SELECT COUNT(*) FROM policy WHERE account_id = ? AND deleted = 0 AND status = 'Active'");
            $row->execute([$accountId]);
            $activeCount = (int)$row->fetchColumn();
            $activeRatio = $policyCount > 0 ? $activeCount / $policyCount : 0;

            $policyPts = min(25, ($policyCount / 4) * 25);
            $premiumPts = min(25, ($totalPremium / 10000) * 25);
            $lobPts = min(20, ($lobCount / 4) * 20);
            $tenurePts = min(15, ($tenureYears / 5) * 15);
            $activePts = $activeRatio * 15;

            $totalScore = (int)round($policyPts + $premiumPts + $lobPts + $tenurePts + $activePts);
            $totalScore = max(0, min(100, $totalScore));

            $breakdown = json_encode([
                'policyCount' => ['value' => $policyCount, 'points' => round($policyPts, 1), 'max' => 25],
                'totalPremium' => ['value' => round($totalPremium, 2), 'points' => round($premiumPts, 1), 'max' => 25],
                'lobDiversity' => ['value' => $lobCount, 'points' => round($lobPts, 1), 'max' => 20],
                'tenureYears' => ['value' => round($tenureYears, 1), 'points' => round($tenurePts, 1), 'max' => 15],
                'activeRatio' => ['value' => round($activeRatio * 100), 'points' => round($activePts, 1), 'max' => 15],
            ]);

            $updateStmt->execute([$totalScore, $breakdown, $accountId]);
        }

        $GLOBALS['log']->info("RecalculateAccountScores: Updated " . count($accounts) . " accounts.");
    }
}
