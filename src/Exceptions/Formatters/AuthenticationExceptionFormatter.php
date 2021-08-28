<?php

namespace Gruz\FPBX\Exceptions\Formatters;

use Throwable;
use Illuminate\Http\JsonResponse;
use Gruz\FPBX\Exceptions\Formatters\ExceptionFormatter;

class AuthenticationExceptionFormatter extends ExceptionFormatter
{
    public function format(JsonResponse $response, Throwable $e, array $reporterResponses)
    {
        $statusCode = 401;
        $response->setStatusCode($statusCode);
        $data = $this->formatData($response->getData(true), $e);

        $response->setData($data);
    }
}
