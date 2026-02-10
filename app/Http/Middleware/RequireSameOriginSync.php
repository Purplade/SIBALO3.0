<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireSameOriginSync
{
    public function handle(Request $request, Closure $next)
    {
        // Lightweight CSRF alternative for offline-sync endpoints when CSRF is disabled:
        // - require a custom header
        // - enforce same-origin when Origin/Referer are present
        $marker = (string) $request->header('X-SIBALO-SYNC', '');
        if ($marker !== '1') {
            return response()->json(['status' => 'error', 'message' => 'Missing sync marker'], 403);
        }

        $host = $request->getSchemeAndHttpHost();
        $origin = (string) $request->header('Origin', '');
        if ($origin !== '' && $origin !== $host) {
            return response()->json(['status' => 'error', 'message' => 'Invalid origin'], 403);
        }

        $referer = (string) $request->header('Referer', '');
        if ($referer !== '') {
            $refHost = parse_url($referer, PHP_URL_SCHEME) . '://' . parse_url($referer, PHP_URL_HOST);
            $refPort = parse_url($referer, PHP_URL_PORT);
            if ($refPort) {
                $refHost .= ':' . $refPort;
            }
            if (!empty(parse_url($referer, PHP_URL_HOST)) && $refHost !== $host) {
                return response()->json(['status' => 'error', 'message' => 'Invalid referer'], 403);
            }
        }

        return $next($request);
    }
}

