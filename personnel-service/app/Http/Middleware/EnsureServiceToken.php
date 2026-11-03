<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureServiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('personnel.api_token');
        $providedToken = (string) ($request->bearerToken() ?? '');

        if ($expectedToken === '' || $providedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'message' => 'API erisimi icin gecerli bir Bearer token gerekli.',
                'error_code' => 'UNAUTHORIZED',
            ], 401);
        }

        return $next($request);
    }
}
