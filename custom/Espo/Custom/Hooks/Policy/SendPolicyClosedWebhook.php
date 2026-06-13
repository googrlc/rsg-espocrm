<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;
use Espo\Custom\Classes\Policy\PolicyClosedWebhookDispatcher;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Bridge trigger: push a Policy to the commissions engine on the lifecycle
 * events that affect commission:
 *
 *  - closed/won (default: Active)  -> engine computes expected commission;
 *  - cancelled (default: Cancelled, Flat Cancel, Lapsed) -> engine reverses it.
 *
 * For closed policies it also re-pushes whenever the AMS later corrects a
 * financial/identity field (the AMS is the system of record and lands data in
 * stages). The dispatcher only POSTs when a watched field actually changed; the
 * engine upserts by `crmPolicyId`, so every re-push is idempotent.
 */
class SendPolicyClosedWebhook implements AfterSave
{
    public function __construct(
        private Config $config,
        private PolicyClosedWebhookDispatcher $policyClosedWebhookDispatcher,
        private Log $log
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $status = trim((string) ($entity->get('status') ?? ''));

        // Only statuses that affect commission: closed (expected) or cancelled
        // (reversal). Everything else (Up for Renewal, Expired, ...) is ignored.
        if (!$this->policyClosedWebhookDispatcher->isRelevantStatus($status)) {
            return;
        }

        $result = $this->policyClosedWebhookDispatcher->dispatch($entity);

        if ($result['dispatched'] === true) {
            return;
        }

        // No watched field changed — nothing to send, not a failure.
        $changes = $result['changes'] ?? [];
        if (!is_array($changes) || $changes === []) {
            return;
        }

        // Had changes but did not POST: an unset URL means the bridge is
        // disabled (fine); a set URL that failed is worth a warning.
        if (trim((string) ($this->config->get('commissionEngineWebhookUrl') ?? '')) === '') {
            return;
        }

        $this->log->warning(sprintf(
            'CommissionEngineBridge: failed to push Policy %s (status "%s") to the commissions engine.',
            (string) ($entity->getId() ?? ''),
            $status
        ));
    }
}
