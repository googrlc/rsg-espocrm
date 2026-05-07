<?php

namespace Espo\Custom\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Tools\GlobalSearch\Service;

class GetGlobalSearch implements Action
{
    public function __construct(
        private Service $service,
    ) {}

    public function process(Request $request): Response
    {
        $query = $request->getQueryParam('q');

        if ($query === null || $query === '') {
            throw new BadRequest("No `q` parameter.");
        }

        $offset = $this->getIntParam($request, 'offset', 0, 0);
        $maxSize = $this->getIntParam($request, 'maxSize', null, 1);

        $result = $this->service->find($query, $offset, $maxSize);

        return ResponseComposer::json($result->toApiOutput());
    }

    private function getIntParam(Request $request, string $name, ?int $default, int $min): ?int
    {
        $value = $request->getQueryParam($name);

        if ($value === null || $value === '') {
            return $default;
        }

        if (!is_numeric($value) || (string) (int) $value !== (string) $value) {
            throw new BadRequest("Bad `$name` parameter.");
        }

        $value = (int) $value;

        if ($value < $min) {
            throw new BadRequest("Bad `$name` parameter.");
        }

        return $value;
    }
}
