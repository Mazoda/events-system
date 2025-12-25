<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantProfileController extends Controller
{
    public function show(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $tenant = Tenant::query()->findOrFail($user->tenant_id);
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => $tenant,
        ]);
    }

    public function update(UpdateTenantRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $tenant = Tenant::query()->findOrFail($user->tenant_id);
        $this->authorize('update', $tenant);

        $tenant->fill($request->validated());
        $tenant->save();

        return response()->json([
            'data' => $tenant->refresh(),
        ]);
    }
}
