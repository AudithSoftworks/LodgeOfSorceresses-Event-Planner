<?php namespace App\Http\Middleware;

use Closure;
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
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            if ($request->expectsJson()) {
                throw $e;
            }

            return redirect()->guest(route('oauth.to', 'discord', false));
        }

        return $next($request);
    }
}
