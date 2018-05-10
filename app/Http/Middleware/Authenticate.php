<?php namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use \Illuminate\Auth\Middleware\Authenticate as IlluminateAuthenticateMiddleware;

class Authenticate extends IlluminateAuthenticateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string[]                 ...$guards
     *
     * @return mixed
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        if (!app('auth.driver')->check()) {
            return redirect()->guest(route('oauth.to', 'ips', false));
        }

        return $next($request);
    }
}
