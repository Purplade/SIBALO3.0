<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Redirect unauthenticated users based on route segment (guard-aware)
        $middleware->redirectGuestsTo(function ($request) {
            $first = $request->segment(1);
            $adminSegments = [
                'dashboard', 'admin', 'pegawai', 'monitoring',
                'laporanabsensi', 'rekap', 'konfigurasilokasi', 'izinsakit'
            ];
            return in_array($first, $adminSegments, true) ? '/panel' : '/login';
        });

        // Add no-cache headers to all web responses to prevent back navigation showing protected pages
        $middleware->appendToGroup('web', [\App\Http\Middleware\NoCache::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
