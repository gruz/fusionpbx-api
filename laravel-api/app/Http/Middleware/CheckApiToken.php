<?php

namespace App\Http\Middleware;

use Closure;
use Response;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('x-apitoken');

        if (!$token) {
            return Response::json(['error' => 'API token was not found']);
        }

        if ($token !== config('fpbx.api_token')) {
            return Response::json(array('error' => 'API token is not correct'));
        }

        return $next($request);
    }
}
