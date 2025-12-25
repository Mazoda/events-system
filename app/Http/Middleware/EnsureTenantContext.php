<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check for Authentication
        if (!Auth::check()) {
            abort(401, 'Unauthenticated');

        }
        $user = Auth::user();
        if ($user->isSuperAdmin() || $user->tenant_id == null) {

            return $next($request);
        }
        // If the user is logged in but has no tenant_id, there's a configuration error.
        if (!$user->tenant_id) {
            abort(403, 'Access denied. User is not assigned to a tenant.');
        }

        $tenant = Tenant::query()->find($user->tenant_id);
        if (!$tenant || !$tenant->is_active) {
            abort(403, 'Access denied. Tenant is inactive.');
        }
        // Success: The user is authenticated, has a tenant_id, and is not a Super Admin.
        // The request can now proceed to the controller.
        // NOTE: The Global Scope already ensures they only see their data.
        return $next($request);

    }
}
