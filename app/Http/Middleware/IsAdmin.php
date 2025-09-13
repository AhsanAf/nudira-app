<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $u = $request->user();
        if (!$u || $u->role !== 'admin') {
            abort(403); // atau redirect()->route('home')->with('error','Akses ditolak.')
        }
        return $next($request);
    }
}
