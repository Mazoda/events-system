<?php

namespace App\Models\Scopes;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        // Super admin (or any user without tenant assignment) sees across tenants.
        if (!$user->tenant_id) {
            return;
        }

        if ($user->tenant_id) {
            // 2. The user is a Tenant Admin or User, so filter records by their tenant_id.
            // This assumes the model being queried has a 'tenant_id' column.
            $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
        }
        // 3. IMPORTANT: If the user is a Super Admin (tenant_id is null) or not logged in,
        // we DO NOT apply the scope, allowing them to see all tenants (or no filtering if guest)
    }
}
