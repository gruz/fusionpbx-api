<?php

namespace Gruz\FPBX\Exceptions\Formatters;

use Throwable;
use Illuminate\Http\JsonResponse;
use Optimus\Heimdal\Formatters\BaseFormatter;

class AuthenticationExceptionFormatter extends BaseFormatter
{
    public function format(JsonResponse $response, Throwable $e, array $reporterResponses)
    {
        $statusCode = 401;
        $response->setStatusCode($statusCode);
        $data = [
            'status' => $statusCode,
            'code'   => $e->getCode(),
            'message'   => $e->getMessage(),
        ];

        if ($this->debug) {
            $data = array_merge($data, [
                'exception' => (string) $e,
                'line'   => $e->getLine(),
                'file'   => $e->getFile()
            ]);
        }

        $response->setData([
            'errors' => [ $data ]
        ]);


        return $response;
    }
}
