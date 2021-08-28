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
        }
        if (empty($statusCode)) {
            $statusCode = 500;
        }
        $response->setStatusCode($statusCode);
        $data = $this->formatData($response->getData(true), $e);

        $response->setData($data);
    }

    protected function formatData($data, $e) {
        if ($this->debug) {
            $data = array_merge($data, [
                'code'   => $e->getCode(),
                'message'   => $e->getMessage(),
                'line'   => $e->getLine(),
                'file'   => $e->getFile(),
                'exception' => explode(PHP_EOL, $e->__toString()),
            ]);
        } else {
            $data = array_merge($data, [
                'code'   => $e->getCode(),
                'message'   => $e->getMessage(),
            ]);
        }

        return $data;
    }
}
