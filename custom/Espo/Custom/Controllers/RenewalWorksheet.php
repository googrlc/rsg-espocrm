<?php

namespace Espo\Custom\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Controllers\Record;
use Espo\Custom\Services\RenewalWorksheetSubmit;

/**
 * Renewal Loop v6 — SubmitWorksheet custom action.
 * Endpoint: POST /api/v1/RenewalWorksheet/action/submit  body: { "id": "<worksheetId>" }
 */
class RenewalWorksheet extends Record
{
    public function postActionSubmit(Request $request): array
    {
        $id = (string) $request->getParam('id');
        if ($id === '') {
            $body = (array) $request->getParsedBody();
            $id = (string) ($body['id'] ?? '');
        }
        if ($id === '') {
            throw new \Espo\Core\Exceptions\BadRequest('Missing worksheet id.');
        }

        $service = new RenewalWorksheetSubmit($this->getEntityManager());

        return $service->submit($id);
    }
}
