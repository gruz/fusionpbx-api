<?php

namespace Gruz\FPBX\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        $middleware = Route::getCurrentRoute()->middleware();

        $expectsJson = $request->expectsJson();
        if (is_array($middleware) && in_array("api", $middleware) || $middleware == "api") {
            $expectsJson = true;
        }

        if (! $expectsJson) {
            return route('login');
        }
    }
}
