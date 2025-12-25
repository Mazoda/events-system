<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketOrder;

class TicketOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Tenant-scoped by global scope.
        return response()->json([
            'data' => TicketOrder::query()->latest('id')->paginate(50),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TicketOrder $ticketOrder)
    {
        return response()->json([
            'data' => $ticketOrder,
        ]);
    }
}
