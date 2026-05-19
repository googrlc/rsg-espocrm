<?php

namespace Espo\Custom\Hooks\Account;

use Espo\Custom\Classes\Hook\NormalizeProperNamesBase;

class NormalizeProperNames extends NormalizeProperNamesBase
{
    protected function getFields(): array
    {
        return [
            'name',
            'primaryFirstName',
            'primaryLastName',
            'spouseFirstName',
            'spouseLastName',
            'csrName',
        ];
    }
}
