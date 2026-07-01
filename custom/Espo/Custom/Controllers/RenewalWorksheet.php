<?php

namespace Espo\Custom\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\BadRequest;
use Espo\Custom\Services\RenewalWorksheetSubmit;

class RenewalWorksheet extends Record
{
    /**
     * POST /api/v1/RenewalWorksheet/action/submit
     *
     * Body: { "id": "<worksheetId>" }
     *
     * Derives completion_type from populated fields and marks the worksheet completed.
     */
    public function postActionSubmit(Request $request, Response $response): mixed
    {
        $data = $request->getParsedBody();
        $worksheetId = trim((string) ($data->id ?? ''));

        if ($worksheetId === '') {
            throw new BadRequest('Missing required field: id');
        }

        /** @var RenewalWorksheetSubmit $service */
        $service = $this->injectableFactory->create(RenewalWorksheetSubmit::class);

        return $service->submit($worksheetId);
    }
}
