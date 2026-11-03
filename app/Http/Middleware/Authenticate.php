<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            return parent::handle($request, $next, ...$guards);
        } catch (AuthenticationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            throw $e;
        }
    }

    /**
     * Override redirectTo to avoid generating redirect responses for API.
     */
    protected function redirectTo($request)
    {
        // If the request expects JSON or is an API route, don't redirect.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // Let the parent behavior occur for non-API web requests (optional).
        return null;
    }
}
