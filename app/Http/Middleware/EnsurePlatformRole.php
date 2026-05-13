<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePlatformRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (! $request->user()?->isPlatformStaff($roles)) {
            abort(403);
        }

        return $next($request);
    }
}
