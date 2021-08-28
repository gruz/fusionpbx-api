<?php

namespace Gruz\FPBX\Exceptions\Formatters;

use Throwable;
use Illuminate\Http\JsonResponse;
use Gruz\FPBX\Exceptions\Formatters\ExceptionFormatter;

class HttpExceptionFormatter extends ExceptionFormatter
{
    public function format(JsonResponse $response, Throwable $e, array $reporterResponses)
    {
        parent::format($response, $e, $reporterResponses);

        if (count($headers = $e->getHeaders())) {
            $response->headers->add($headers);
        }

        $response->setStatusCode($e->getStatusCode());
    }
}
