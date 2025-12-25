<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\AttendeeTicket;
use App\Models\Tenant;

class TenantAttendeeTicketsController extends Controller
{
    public function index(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => AttendeeTicket::query()
                ->withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->latest('id')
                ->paginate(50),
        ]);
    }
}
