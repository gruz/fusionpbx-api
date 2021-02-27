<?php

namespace Infrastructure\Http\Middleware;

use Closure;
use Infrastructure\SwaggerProcessors\LoadConstantsHelper;

class L5SwaggerLoadConstants
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws L5SwaggerException
     */
    public function handle($request, Closure $next)
    {
        new LoadConstantsHelper($request->getPathInfo());

        return $next($request);
    }

}
