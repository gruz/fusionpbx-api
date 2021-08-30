<?php

namespace Gruz\FPBX\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class Localization
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
        $locale = $request->header('x-locale');
        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
