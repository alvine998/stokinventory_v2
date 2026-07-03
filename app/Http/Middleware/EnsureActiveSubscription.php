<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->isPlatformStaff()) {
            return $next($request);
        }

        $business = $user->business;

        if (! $business) {
            return $next($request);
        }

        if ($business->isTrialExpired() && ! $request->routeIs('expired', 'logout', 'billing.*', 'order.*')) {
            if (! $business->trial_expired_at) {
                $business->update(['trial_expired_at' => now()]);
            }

            return redirect()->route('expired');
        }

        return $next($request);
    }
}
