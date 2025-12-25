<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketType\StoreTicketTypeRequest;
use App\Http\Requests\TicketType\UpdateTicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;

class TicketTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $this->authorize('view', $event);
        $this->authorize('viewAny', TicketType::class);

        return response()->json([
            'data' => TicketType::query()
                ->where('event_id', $event->id)
                ->latest('id')
                ->paginate(50),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketTypeRequest $request, Event $event)
    {
        $this->authorize('view', $event);
        $this->authorize('create', TicketType::class);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $ticketType = TicketType::query()->create(array_merge(
            $request->validated(),
            [
                'tenant_id' => $user->tenant_id,
                'event_id' => $event->id,
                'quantity_sold' => 0,
            ],
        ));

        return response()->json([
            'data' => $ticketType,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, TicketType $ticketType)
    {
        $this->authorize('view', $event);
        $this->authorize('view', $ticketType);

        if ($ticketType->event_id !== $event->id) {
            abort(404);
        }

        return response()->json([
            'data' => $ticketType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketTypeRequest $request, Event $event, TicketType $ticketType)
    {
        $this->authorize('view', $event);
        $this->authorize('update', $ticketType);

        if ($ticketType->event_id !== $event->id) {
            abort(404);
        }

        $ticketType->fill($request->validated());
        $ticketType->save();

        return response()->json([
            'data' => $ticketType->refresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, TicketType $ticketType)
    {
        $this->authorize('view', $event);
        $this->authorize('delete', $ticketType);

        if ($ticketType->event_id !== $event->id) {
            abort(404);
        }

        $ticketType->delete();

        return response()->json([], 204);
    }
}
