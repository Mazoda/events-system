<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\PurchaseRequest;
use App\Models\TicketOrder;
use App\Services\TicketPurchaseService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'data' => TicketOrder::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->paginate(50),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $order = app(TicketPurchaseService::class)->purchase($user, $request->validated('items'));

        return response()->json([
            'data' => $order,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, TicketOrder $ticketOrder)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($ticketOrder->user_id !== $user->id) {
            abort(404);
        }

        return response()->json([
            'data' => $ticketOrder,
        ]);
    }
}
