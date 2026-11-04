<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('personnel_admin') !== true) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Oturum süresi doldu.'], 401)
                : redirect()->guest(route('login'))->with('warning', 'Devam etmek için giriş yapın.');
        }
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');
        return $response;
    }
}
