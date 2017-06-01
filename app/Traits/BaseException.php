<?php
namespace App\Traits;

trait BaseException
{
    public function __construct($message = null, \Exception $previous = null, $code = null)
    {
      $errors = config('errors');

      if (is_array($message))
      {
        $message = __($errors[static::class]['message'], $message);
      }

      foreach (['message', 'code'] as $v)
      {
        $$v = is_null($$v) ? $errors[static::class][$v] : $$v;
      }

      /*
       * Since Exceptions extend base Exceptions which has different number
       * of parameters in theit __construct methods, I use this trick
       * to pass the corret number of parameters.
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

      foreach ($params as $param)
      {
        if (!isset(${$param->name}))
        {
          $parameters[] = null;
        }
        else
        {
          $parameters[] = ${$param->name};
        }
      }

      call_user_func_array(array($this, 'parent::__construct'), $parameters);
    }
}