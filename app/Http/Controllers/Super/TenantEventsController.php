<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Tenant;

class TenantEventsController extends Controller
{
    public function index(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => Event::query()
                ->withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->latest('id')
                ->paginate(50),
        ]);
    }
}
