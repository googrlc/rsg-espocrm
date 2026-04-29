<?php
namespace Espo\Custom\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\Entity;

class Account extends \Espo\Modules\Crm\Controllers\Account
{
    public function postActionRunIntelPack(Request $request): object
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest("Missing account ID.");
        }

        $entityManager = $this->getContainer()->getByClass(\Espo\ORM\EntityManager::class);
        $account = $entityManager->getEntityById('Account', $data->id);

        if (!$account) {
            throw new BadRequest("Account not found.");
        }

        // Webhook URL + HMAC secret (sign raw JSON body; n8n verifies X-Intel-Pack-Signature)
        $config = $this->getContainer()->getByClass(\Espo\Core\Utils\Config::class);
        $webhookUrl = trim((string) ($config->get('intelPackWebhookUrl') ?? ''));
        $webhookSecret = trim((string) ($config->get('intelPackWebhookSecret') ?? ''));

        if ($webhookUrl === '') {
            return (object) [
                'success' => false,
                'message' => 'Intel Pack webhook URL not configured. Set intelPackWebhookUrl in config.'
            ];
        }

        if ($webhookSecret === '') {
            return (object) [
                'success' => false,
                'message' => 'Intel Pack webhook secret not configured. Set intelPackWebhookSecret in config.'
            ];
        }

        // Resolve assigned user name
        $assignedUserName = '';
        $assignedUserId = $account->get('assignedUserId');
        if ($assignedUserId) {
            $user = $entityManager->getEntityById('User', $assignedUserId);
            if ($user) {
                $assignedUserName = $user->get('name');
            }
        }

        $payload = json_encode([
            'entityType' => 'Account',
            'entityId' => $account->get('id'),
            'assignedUserName' => $assignedUserName,
            'momentumId' => $account->get('momentum_client_id') ?? '',
        ]);

        if ($payload === false) {
            throw new BadRequest('Failed to encode Intel Pack payload.');
        }

        $signature = 'sha256=' . hash_hmac('sha256', $payload, $webhookSecret);

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Intel-Pack-Signature: ' . $signature,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return (object) ['success' => true, 'message' => 'Intel Pack triggered.'];
        }

        return (object) ['success' => false, 'message' => 'Webhook returned HTTP ' . $httpCode];
    }

    public function postActionCommissionSummary(Request $request): object
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest("Missing account ID.");
        }

        $entityManager = $this->getContainer()->getByClass(\Espo\ORM\EntityManager::class);
        $account = $entityManager->getEntityById('Account', $data->id);

        if (!$account) {
            throw new BadRequest("Account not found.");
        }

        $commissionList = $entityManager
            ->getRDBRepository('Commission')
            ->where([
                'accountId' => $account->getId(),
                'deleted' => false,
            ])
            ->find();

        $summary = [
            'count' => 0,
            'estimatedCount' => 0,
            'postedCount' => 0,
            'overdueCount' => 0,
            'pipelineAmount' => 0.0,
            'earnedAmount' => 0.0,
            'overdueAmount' => 0.0,
            'lastPaymentDate' => null,
            'nextExpectedDate' => null,
            'latestCommissions' => [],
        ];

        foreach ($commissionList as $commission) {
            $summary['count']++;

            $status = (string) ($commission->get('status') ?? '');
            $estimatedCommission = (float) ($commission->get('estimatedCommission') ?? 0);
            $postedAmount = (float) ($commission->get('postedAmount') ?? 0);
            $paymentReceivedDate = $this->normalizeDate($commission->get('paymentReceivedDate'));
            $expectedPaymentDate = $this->normalizeDate($commission->get('expectedPaymentDate'));

            if ($status === 'Posted') {
                $summary['postedCount']++;
                $summary['earnedAmount'] += $postedAmount > 0 ? $postedAmount : $estimatedCommission;

                if ($paymentReceivedDate && (!$summary['lastPaymentDate'] || $paymentReceivedDate > $summary['lastPaymentDate'])) {
                    $summary['lastPaymentDate'] = $paymentReceivedDate;
                }
            } elseif ($status === 'Overdue') {
                $summary['overdueCount']++;
                $summary['pipelineAmount'] += $estimatedCommission;
                $summary['overdueAmount'] += $estimatedCommission;

                if ($expectedPaymentDate && (!$summary['nextExpectedDate'] || $expectedPaymentDate < $summary['nextExpectedDate'])) {
                    $summary['nextExpectedDate'] = $expectedPaymentDate;
                }
            } else {
                $summary['estimatedCount']++;
                $summary['pipelineAmount'] += $estimatedCommission;

                if ($expectedPaymentDate && (!$summary['nextExpectedDate'] || $expectedPaymentDate < $summary['nextExpectedDate'])) {
                    $summary['nextExpectedDate'] = $expectedPaymentDate;
                }
            }

            $summary['latestCommissions'][] = $this->formatCommissionPreview($commission);
        }

        usort(
            $summary['latestCommissions'],
            fn (array $a, array $b): int => strcmp($b['sortDate'], $a['sortDate'])
        );

        $summary['latestCommissions'] = array_slice($summary['latestCommissions'], 0, 3);

        return (object) $summary;
    }

    private function formatCommissionPreview(Entity $commission): array
    {
        $sortDate = $this->normalizeDate(
            $commission->get('paymentReceivedDate')
            ?: $commission->get('expectedPaymentDate')
            ?: $commission->get('createdAt')
        ) ?? '0000-00-00';

        return [
            'name' => (string) ($commission->get('name') ?? 'Commission'),
            'status' => (string) ($commission->get('status') ?? ''),
            'amount' => $this->resolveCommissionDisplayAmount($commission),
            'sortDate' => $sortDate,
            'displayDate' => $this->normalizeDate(
                $commission->get('paymentReceivedDate')
                ?: $commission->get('expectedPaymentDate')
            ),
        ];
    }

    private function resolveCommissionDisplayAmount(Entity $commission): float
    {
        $status = (string) ($commission->get('status') ?? '');
        $postedAmount = (float) ($commission->get('postedAmount') ?? 0);
        $estimatedCommission = (float) ($commission->get('estimatedCommission') ?? 0);

        if ($status === 'Posted' && $postedAmount > 0) {
            return $postedAmount;
        }

        return $estimatedCommission;
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        return substr((string) $value, 0, 10);
    }
}
