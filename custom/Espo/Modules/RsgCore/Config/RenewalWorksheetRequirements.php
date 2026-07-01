<?php

namespace Espo\Modules\RsgCore\Config;

/**
 * Renewal Loop v6 §4.7 — per-LOB completion requirements for RenewalWorksheet.
 *
 * SubmitWorksheet derives ``completion_type`` from how many variant fields are
 * attested: zero variant fields set -> contact_only; ALL required fields set ->
 * full_review; a partial set -> reject (actionable message). Booleans count as
 * "set" only when true; enum fields count as "set" when non-empty and non-``na``.
 *
 * Field names are the v5-locked LOB-prefixed attestation names (see rsg-espocrm
 * PR #34). The required sets mirror the table locked in the PR #34 audit.
 */
final class RenewalWorksheetRequirements
{
    /** "Not applicable" sentinel for enum attestations — does not count as set. */
    public const NA_VALUE = 'na';

    /** Enum attestations whose "set" semantics are non-empty AND non-na. */
    public const ENUM_FIELDS = [
        'pa_payment_plan_confirmed',
        'ca_radius_of_use_confirmed',
        'gl_exposure_basis_confirmed',
        'wc_officers_treatment_reviewed',
    ];

    /** Every variant field by lob_variant (drives the "zero set" check). */
    public const VARIANT_FIELDS = [
        'personal_auto' => [
            'pa_driver_list_confirmed', 'pa_vehicle_list_confirmed', 'pa_garaging_address_confirmed',
            'pa_coverage_limits_reviewed', 'pa_umbrella_offered', 'pa_payment_plan_confirmed',
        ],
        'homeowners' => [
            'ho_dwelling_amount_reviewed', 'ho_replacement_cost_confirmed', 'ho_roof_age_verified',
            'ho_roof_age_years', 'ho_scheduled_items_reviewed', 'ho_umbrella_offered', 'ho_flood_reviewed',
        ],
        'commercial_auto' => [
            'ca_driver_list_confirmed', 'ca_vehicle_list_confirmed', 'ca_radius_of_use_confirmed',
            'ca_hired_non_owned_reviewed', 'ca_coverage_limits_reviewed', 'ca_mvr_reviewed',
        ],
        'general_liability' => [
            'gl_operations_description_confirmed', 'gl_exposure_basis_confirmed', 'gl_exposure_value_reviewed',
            'gl_subcontractor_use_reviewed', 'gl_additional_insureds_reviewed', 'gl_waiver_of_subrogation_needed',
        ],
        'workers_comp' => [
            'wc_class_codes_confirmed', 'wc_payroll_by_class_confirmed', 'wc_officers_treatment_reviewed',
            'wc_experience_mod_reviewed', 'wc_experience_mod_value', 'wc_certificates_of_insurance_current',
        ],
        'default' => ['def_coverage_reviewed', 'def_account_details_confirmed'],
    ];

    /** Required subset that gates the full_review completion path. */
    public const REQUIRED_FOR_FULL_REVIEW = [
        'personal_auto' => [
            'pa_driver_list_confirmed', 'pa_vehicle_list_confirmed',
            'pa_garaging_address_confirmed', 'pa_coverage_limits_reviewed',
        ],
        'homeowners' => [
            'ho_dwelling_amount_reviewed', 'ho_replacement_cost_confirmed', 'ho_scheduled_items_reviewed',
        ],
        'commercial_auto' => [
            'ca_driver_list_confirmed', 'ca_vehicle_list_confirmed', 'ca_coverage_limits_reviewed',
        ],
        'general_liability' => [
            'gl_operations_description_confirmed', 'gl_exposure_basis_confirmed',
            'gl_exposure_value_reviewed', 'gl_additional_insureds_reviewed',
        ],
        'workers_comp' => [
            'wc_class_codes_confirmed', 'wc_payroll_by_class_confirmed', 'wc_officers_treatment_reviewed',
        ],
        'default' => ['def_coverage_reviewed', 'def_account_details_confirmed'],
    ];

    /** @return string[] */
    public static function fieldsForVariant(string $variant): array
    {
        return self::VARIANT_FIELDS[$variant] ?? self::VARIANT_FIELDS['default'];
    }

    /** @return string[] */
    public static function requiredForVariant(string $variant): array
    {
        return self::REQUIRED_FOR_FULL_REVIEW[$variant] ?? self::REQUIRED_FOR_FULL_REVIEW['default'];
    }

    /** Line of business (Espo enum label) -> lob_variant discriminator. */
    public static function lobVariantFromLineOfBusiness(string $lineOfBusiness): string
    {
        return match ($lineOfBusiness) {
            'Personal Auto' => 'personal_auto',
            'Homeowners' => 'homeowners',
            'Commercial Auto' => 'commercial_auto',
            'General Liability' => 'general_liability',
            'Workers Comp' => 'workers_comp',
            default => 'default',
        };
    }
}
