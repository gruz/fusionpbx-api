<?php

namespace Gruz\FPBX\Exceptions\Formatters;

use Throwable;
use Illuminate\Http\JsonResponse;
use Optimus\Heimdal\Formatters\BaseFormatter;

class ExceptionFormatter extends BaseFormatter
{
    public function format(JsonResponse $response, Throwable $e, array $reporterResponses)
    {
        // dd($e->getCode(), $e->getStatusCode());
        $statusCode = 500;
        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        } elseif ( $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            $statusCode = 403;
        }

        if (empty($statusCode)) {
            $statusCode = 500;
        }
        $response->setStatusCode($statusCode);
        $data = $this->formatData($response, $e);

        $response->setData($data);
    }

    protected function formatData($response, $e) {
        $data = $response->getData(true);
        $code = $e->getCode();
        if (empty($code)) {
            $code = $response->getStatusCode();
        }
        if ($this->debug) {
            $data = array_merge($data, [
                'code'   => $code,
                'message'   => $e->getMessage(),
                'line'   => $e->getLine(),
                'file'   => $e->getFile(),
                'exception' => explode(PHP_EOL, $e->__toString()),
            ]);
        } else {
            $data = array_merge($data, [
                'code'   => $code,
                'message'   => $e->getMessage(),
            ]);
        }

        return $data;
    }
}
