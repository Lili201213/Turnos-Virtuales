<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SoloAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || $request->user()->rol !== 'admin') {
            abort(403, 'Acceso solo para administradores.');
        }

        return $next($request);
    }
}
