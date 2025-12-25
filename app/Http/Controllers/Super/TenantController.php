<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Models\Tenant;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Tenant::class);

        return response()->json([
            'data' => Tenant::query()->latest('id')->paginate(50),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantRequest $request)
    {
        $this->authorize('create', Tenant::class);

        $tenant = Tenant::query()->create([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'logo_url' => $request->validated('logo_url') ?? '',
            'is_active' => $request->validated('is_active', true),
        ]);

        return response()->json([
            'data' => $tenant,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => $tenant,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $tenant->fill($request->validated());
        $tenant->save();

        return response()->json([
            'data' => $tenant->refresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        $this->authorize('delete', $tenant);

        $tenant->delete();

        return response()->json([], 204);
    }
}
