<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Izinkan hanya user role=admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            // pilih salah satu: abort 403 atau redirect ke dashboard
            // abort(403, 'Anda tidak punya akses.');
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak punya akses ke halaman admin.');
        }

        return $next($request);
    }
}
