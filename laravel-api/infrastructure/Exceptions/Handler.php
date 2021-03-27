<?php

namespace Infrastructure\Exceptions;

use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Optimus\Heimdal\ExceptionHandler;
use Route;
use Illuminate\Foundation\Exceptions\Handler as LaravelExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {

        if ($this->isHttpException($e)) {
            if ($e instanceof \Api\User\Exceptions\ActivationHashNotFoundException) {
                return response()->view('front.activation', ['message' => $e->getMessage()], 200);
            }

            // ~ return $this->renderHttpException($e);
        }

        $middleware = Route::getCurrentRoute()->middleware();
        if (
            is_array($middleware) && in_array("web", $middleware) ||
            $middleware == "web"
        ) {
            $handler = new LaravelExceptionHandler($this->container);

            return $handler->render($request, $e);
        }

        return parent::render($request, $e);
    }
}