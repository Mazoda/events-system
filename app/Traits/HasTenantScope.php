<?php
namespace App\Traits;

use App\Models\Scopes\TenantScope;

trait HasTenantScope
{
    protected static function bootHasTenantScope(): void
    {
        // Always attach the scope. The scope itself decides whether to filter.
        static::addGlobalScope(new TenantScope());
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