<?php
namespace App\Traits;

use App\Models\Scopes\TenantScope;
use Auth;
trait HasTenantScope
{
    protected static function bootHasTenantScope(): void
    {
        $user = Auth::user();
        // We only want to apply the scope if the user is authenticated.
        if ($user) {

            // Check if the authenticated user is a Super Admin.
            // Assuming your User model has an isSuperAdmin() helper method or checks role == 'super_admin'.
            // Also bypass if tenant_id is null, which often signifies the Super Admin.
            if ($user->isSuperAdmin()) {
                return;
            }
        }

        // Apply the TenantScope to the model.
        // Apply the TenantScope to the model.

        static::addGlobalScope(new TenantScope);
    }

    /**
     * Allow specific queries to explicitly ignore the tenant scope.
     * This can be used internally by the Super Admin if the boot method didn't bypass it.
     */
    public static function withoutTenantScope()
    {
        return static::withoutGlobalScope(TenantScope::class);
    }
}