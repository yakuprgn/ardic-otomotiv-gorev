<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = (string) config('training.admin_username');
        $passwordHash = (string) config('training.admin_password_hash');

        $authenticated = $username !== ''
            && $passwordHash !== ''
            && hash_equals($username, (string) $request->getUser())
            && password_verify((string) $request->getPassword(), $passwordHash);

        if (! $authenticated) {
            return response('Eğitim portalı yönetimi için kimlik doğrulaması gerekli.', 401, [
                'WWW-Authenticate' => 'Basic realm="Egitim Portali", charset="UTF-8"',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $next($request);
    }
}
