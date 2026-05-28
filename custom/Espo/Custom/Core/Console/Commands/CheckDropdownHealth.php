<?php
namespace Espo\Custom\Core\Console\Commands;

use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\IO;
use Espo\Core\Utils\Metadata;

class CheckDropdownHealth implements Command
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    public function run(Params $params, IO $io): void
    {
        $io->writeLine("=== EspoCRM Dropdown Health Check ===\n");

        $knownBroken = [
            'Opportunity' => [
                'stage'              => ['Discovery','Quoting','Markets Out / Shopping','Proposal Presented','Negotiation','Closed Won','Closed Lost'],
                'businessType'       => ['New Business','Renewal','Rewrite'],
                'healthCoverageType' => ['Term','Whole Life','Universal Life','Final Expense','Medicare Supplement','Medicare Advantage','Group','Individual','Other'],
            ],
        ];

        $issues = 0;
        $entityList = array_keys($this->metadata->get(['entityDefs']) ?? []);
        sort($entityList);

        foreach ($entityList as $entityType) {
            $fields = $this->metadata->get(['entityDefs', $entityType, 'fields']) ?? [];

            foreach ($fields as $fieldName => $fieldDefs) {
                $type = $fieldDefs['type'] ?? '';
                if (!in_array($type, ['enum', 'multiEnum', 'array'])) continue;

                $optionList  = $fieldDefs['optionList'] ?? $fieldDefs['options'] ?? null;
                $optionsRef  = $fieldDefs['optionsReference'] ?? null;

                if ($optionsRef !== null) {
                    $refOptions = $this->metadata->get(['app', 'optionDefs', $optionsRef]) ?? null;
                    if ($refOptions === null) {
                        $io->writeLine("❌ {$entityType}.{$fieldName} — BROKEN: references option group '{$optionsRef}' which does not exist");
                        $issues++;
                        continue;
                    }
                    $optionList = array_keys($refOptions);
                }

                if (empty($optionList)) {
                    $io->writeLine("⚠️  {$entityType}.{$fieldName} — EMPTY option list (0 options defined)");
                    $issues++;
                    continue;
                }

                if (isset($knownBroken[$entityType][$fieldName])) {
                    $expected = $knownBroken[$entityType][$fieldName];
                    $missing  = array_diff($expected, $optionList);
                    if (!empty($missing)) {
                        $io->writeLine("❌ {$entityType}.{$fieldName} — MISSING expected options: " . implode(', ', $missing));
                        $issues++;
                        continue;
                    }
                }

                $count = count($optionList);
                $io->writeLine("✅ {$entityType}.{$fieldName} — OK ({$count} options)");
            }
        }

        $io->writeLine("\n=== Summary: " . ($issues === 0 ? "No issues found." : "{$issues} issue(s) detected.") . " ===");

        if ($issues > 0) {
            $io->setExitStatus(1);
        }
    }
}
