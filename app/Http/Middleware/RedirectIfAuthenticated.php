<?php namespace App\Http\Middleware;

use App\Exceptions\Users\UserAlreadyLoggedInException;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     *
     * @throws \App\Exceptions\Users\UserAlreadyLoggedInException
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            if ($request->expectsJson()) {
                throw new UserAlreadyLoggedInException;
            }

            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
