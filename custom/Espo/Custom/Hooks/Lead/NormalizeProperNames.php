<?php

namespace Espo\Custom\Hooks\Lead;

use Espo\Custom\Classes\Hook\NormalizeProperNamesBase;

class NormalizeProperNames extends NormalizeProperNamesBase
{
    protected function getFields(): array
    {
        return [
            'firstName',
            'lastName',
            'accountName',
        ];
    }
}
