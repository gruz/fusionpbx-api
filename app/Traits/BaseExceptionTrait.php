<?php

namespace App\Traits;

trait BaseExceptionTrait
{
    public function __construct($message = null, \Exception $previous = null, $code = null)
    {
        $errors = config('errors');

        if (is_array($message)) {
            $message = __($errors[static::class]['message'], $message);
        }

        foreach (['message', 'code'] as $v) {
            $$v = is_null($$v) ? $errors[static::class][$v] : $$v;
        }

        /*
       * Since Exceptions extend base Exceptions which has different number
       * of parameters in their __construct methods, I use this trick
       * to pass the correct number of parameters.
       * E.g.
       * vendor/symfony/http-kernel/Exception/HttpException.php
       *     public function __construct($statusCode, $message = null, \Exception $previous = null, array $headers = array(), $code = 0)
       *
       * And
       *  vendor/symfony/http-kernel/Exception/UnprocessableEntityHttpException.php
       *     public function __construct($message = null, \Exception $previous = null, $code = 0)
       */
        $r = new \ReflectionClass($this);
        $params = $r->getParentClass()->getConstructor()->getParameters();

        $parameters = [];

        foreach ($params as $param) {
            $type = $param->getType();
            assert($type instanceof \ReflectionNamedType);
            $paramTypeName = $type->getName();
            if (!isset(${$param->name})) {
                switch ($paramTypeName) {
                    case 'array':
                        $value = [];
                        break;
                    default:
                        $value = null;
                        break;
                }
                $parameters[] = $value;
            } else {
                $parameters[] = ${$param->name};
            }
        }

        call_user_func_array(array($this, 'parent::__construct'), $parameters);
    }
}
