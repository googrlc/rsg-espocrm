<?php

namespace Espo\Custom\Hooks\Commission;

use DateInterval;
use DateTimeImmutable;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class DeriveLedgerFields implements BeforeSave
{
    private const RECONCILED_VARIANCE_THRESHOLD_PERCENT = 5.0;
    private const DISPUTED_VARIANCE_THRESHOLD_PERCENT = 15.0;

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $status = (string) ($entity->get('status') ?? 'Estimated');
        if ($status === '') {
            $status = 'Estimated';
            $entity->set('status', $status);
        }

        $today = new DateTimeImmutable('today');
        $expectedPaymentDate = trim((string) ($entity->get('expectedPaymentDate') ?? ''));
        $paymentReceivedDate = trim((string) ($entity->get('paymentReceivedDate') ?? ''));

        if ($paymentReceivedDate !== '' && $status !== 'Posted') {
            $status = 'Posted';
            $entity->set('status', 'Posted');
        }

        if ($status === 'Posted' && $paymentReceivedDate === '') {
            $paymentReceivedDate = $today->format('Y-m-d');
            $entity->set('paymentReceivedDate', $paymentReceivedDate);
        }

        if ($status !== 'Posted' && $this->isOverdueByGraceWindow($expectedPaymentDate, $today)) {
            $status = 'Overdue';
            $entity->set('status', 'Overdue');
        } elseif ($status === 'Overdue' && !$this->isOverdueByGraceWindow($expectedPaymentDate, $today)) {
            $status = 'Estimated';
            $entity->set('status', 'Estimated');
        }

        $entity->set('overdueFlag', $status === 'Overdue');

        $estimatedCommission = (float) ($entity->get('estimatedCommission') ?? 0.0);
        $postedAmount = $entity->get('postedAmount');
        if ($postedAmount !== null && $postedAmount !== '') {
            $varianceAmount = (float) $postedAmount - $estimatedCommission;
            $entity->set('varianceAmount', round($varianceAmount, 2));
            if ($estimatedCommission > 0) {
                $entity->set('variancePercent', round(($varianceAmount / $estimatedCommission) * 100, 2));
            } else {
                $entity->set('variancePercent', null);
            }
        } else {
            $entity->set('varianceAmount', null);
            $entity->set('variancePercent', null);
        }

        $this->syncReconciliationStatus($entity, $status);
        $this->syncLedgerStatus($entity, $today);
    }

    private function syncReconciliationStatus(Entity $entity, string $status): void
    {
        if ($status !== 'Posted') {
            if ((string) ($entity->get('reconciliationStatus') ?? '') === '') {
                $entity->set('reconciliationStatus', 'Unreconciled');
            }

            return;
        }

        $variancePercent = $entity->get('variancePercent');
        if ($variancePercent === null || $variancePercent === '') {
            $entity->set('reconciliationStatus', 'Unreconciled');

            return;
        }

        $absVariancePercent = abs((float) $variancePercent);
        if ($absVariancePercent <= self::RECONCILED_VARIANCE_THRESHOLD_PERCENT) {
            $entity->set('reconciliationStatus', 'Reconciled');

            return;
        }

        if ($absVariancePercent >= self::DISPUTED_VARIANCE_THRESHOLD_PERCENT) {
            $entity->set('reconciliationStatus', 'Disputed');

            return;
        }

        $entity->set('reconciliationStatus', 'Unreconciled');
    }

    private function syncLedgerStatus(Entity $entity, DateTimeImmutable $today): void
    {
        $externalId = trim((string) ($entity->get('ledgerExternalId') ?? ''));
        $syncStatus = (string) ($entity->get('ledgerSyncStatus') ?? '');

        if ($externalId !== '' && $syncStatus !== 'Synced') {
            $entity->set('ledgerSyncStatus', 'Synced');
            $entity->set('ledgerSyncedAt', $today->format('Y-m-d H:i:s'));

            return;
        }

        if ($syncStatus === '') {
            $entity->set('ledgerSyncStatus', 'Pending');
        }
    }

    private function isOverdueByGraceWindow(string $expectedPaymentDate, DateTimeImmutable $today): bool
    {
        if ($expectedPaymentDate === '') {
            return false;
        }

        $expected = new DateTimeImmutable($expectedPaymentDate);
        $graceBoundary = $expected->add(new DateInterval('P30D'));

        return $graceBoundary < $today;
    }
}
