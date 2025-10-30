<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class RefreshCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If session expired, regenerate a fresh CSRF token cookie
        if ($response->getStatusCode() === 419) {
            $token = csrf_token();
            Cookie::queue('XSRF-TOKEN', $token, 120);
        }

        return $response;
    }
}
