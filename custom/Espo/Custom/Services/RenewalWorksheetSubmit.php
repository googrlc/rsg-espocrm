<?php

namespace Espo\Custom\Services;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * Handles the SubmitWorksheet custom action.
 *
 * Derives completion_type from the worksheet's populated fields:
 *   - Zero variant-specific fields populated → contact_only
 *   - All required variant fields set        → full_review
 *   - Partial                                → reject with actionable message
 *
 * On success: sets state = completed, completion_type, submitted_at.
 */
class RenewalWorksheetSubmit
{
    /**
     * Fields required for full_review, keyed by lob_variant.
     * All LOBs share the common fields; variant-specific lists are additive.
     *
     * @var array<string, string[]>
     */
    private const REQUIRED_BY_VARIANT = [
        'commercial_auto' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
            'vehicles_reviewed',
            'drivers_reviewed',
            'loss_runs_obtained',
        ],
        'general_liability' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
            'loss_runs_obtained',
        ],
        'workers_comp' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
            'payroll_updated',
            'loss_runs_obtained',
        ],
        'commercial_property' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
            'property_schedule_reviewed',
            'loss_runs_obtained',
        ],
        'bop' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
            'property_schedule_reviewed',
            'loss_runs_obtained',
        ],
        'professional_liability' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
            'loss_runs_obtained',
        ],
        'umbrella' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
            'loss_runs_obtained',
        ],
        'personal_auto' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
            'vehicles_reviewed',
            'drivers_reviewed',
        ],
        'homeowners' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'coverages_reviewed',
            'contact_confirmed',
        ],
        'life_health' => [
            'declaration_reviewed',
            'account_details_confirmed',
            'renewal_email_sent',
            'ams_updated',
            'contact_confirmed',
        ],
    ];

    private const COMMON_REQUIRED = [
        'declaration_reviewed',
        'account_details_confirmed',
        'renewal_email_sent',
        'ams_updated',
        'contact_confirmed',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    /**
     * @return array{completion_type: string, missing: string[]}
     */
    public function submit(string $worksheetId): array
    {
        $worksheet = $this->entityManager->getEntityById('RenewalWorksheet', $worksheetId);

        if (!$worksheet) {
            throw new NotFound("RenewalWorksheet '$worksheetId' not found.");
        }

        $currentState = (string) ($worksheet->get('state') ?? '');

        if ($currentState === 'completed') {
            return [
                'completion_type' => (string) ($worksheet->get('completion_type') ?? ''),
                'missing'         => [],
            ];
        }

        $completionType = $this->deriveCompletionType($worksheet);

        $worksheet->set([
            'state'           => 'completed',
            'completion_type' => $completionType,
            'submitted_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->entityManager->saveEntity($worksheet, [SaveOption::SILENT => false]);

        return [
            'completion_type' => $completionType,
            'missing'         => [],
        ];
    }

    private function deriveCompletionType(Entity $worksheet): string
    {
        $lobVariant = (string) ($worksheet->get('lob_variant') ?? '');

        $variantFields = self::REQUIRED_BY_VARIANT[$lobVariant] ?? self::COMMON_REQUIRED;

        $populatedVariantFields = array_filter(
            $variantFields,
            fn(string $f) => (bool) $worksheet->get($f)
        );

        if (count($populatedVariantFields) === 0) {
            return 'contact_only';
        }

        $missingFields = array_diff($variantFields, array_keys($populatedVariantFields));

        if (!empty($missingFields)) {
            $humanReadable = implode(', ', array_map(
                fn(string $f) => str_replace('_', ' ', $f),
                $missingFields
            ));

            throw new BadRequest(
                'Worksheet is partially complete. '
                . 'The following fields are required for a full_review worksheet but are not checked: '
                . $humanReadable . '. '
                . 'Either check all required fields (full_review) or submit with only contact information filled (contact_only).'
            );
        }

        return 'full_review';
    }
}
