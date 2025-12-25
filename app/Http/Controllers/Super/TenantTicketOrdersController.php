<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TicketOrder;

class TenantTicketOrdersController extends Controller
{
    public function index(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => TicketOrder::query()
                ->withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->latest('id')
                ->paginate(50),
        ]);
    }
}
