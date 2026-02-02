<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoExpireTenants
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next): Response
    {
        Tenant::where('status', 'active')
            ->whereNotNull('expired_date')
            ->whereDate('expired_date', '<', now())
            ->update(['status' => 'expired']);

        return $next($request);
    }
}
