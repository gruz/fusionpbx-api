<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'logout',
        'stripe/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        $tokenOk = $this->tokensMatch($request);

        if (!$tokenOk) {
            $routes = ["do.login"];
            $route = $request->route()->getName();
    
            // Redirect to custom page if it doesn't relate to a profile
            if (in_array($route, $routes) && Auth::user()) {
                return $next($request);
            }
        }

        return parent::handle($request, $next);
    }
}
