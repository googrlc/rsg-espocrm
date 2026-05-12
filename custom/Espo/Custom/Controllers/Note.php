<?php

namespace Espo\Custom\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Exceptions\Forbidden;
use stdClass;

class Note extends \Espo\Controllers\Note
{
    public function postActionCreate(Request $request, Response $response): stdClass
    {
        throw new Forbidden('Native stream notes are read-only. Use Client Notes.');
    }

    public function patchActionUpdate(Request $request, Response $response): stdClass
    {
        throw new Forbidden('Native stream notes are read-only. Use Client Notes.');
    }

    public function putActionUpdate(Request $request, Response $response): stdClass
    {
        throw new Forbidden('Native stream notes are read-only. Use Client Notes.');
    }

    public function deleteActionDelete(Request $request, Response $response): bool
    {
        throw new Forbidden('Native stream notes are read-only. Use Client Notes.');
    }
}
