<?php namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as IlluminateAuthenticateMiddleware;

class Authenticate extends IlluminateAuthenticateMiddleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    protected function redirectTo($request): ?string
    {
        if (!$request->expectsJson()) {
            return route('oauth.to', 'discord', false);
        }

        return null;
    }
}
