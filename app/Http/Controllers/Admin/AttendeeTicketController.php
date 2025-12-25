<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendeeTicket\ScanRequest;
use App\Models\AttendeeTicket;
use Illuminate\Support\Carbon;

class AttendeeTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', AttendeeTicket::class);

        return response()->json([
            'data' => AttendeeTicket::query()->latest('id')->paginate(50),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendeeTicket $attendeeTicket)
    {
        $this->authorize('view', $attendeeTicket);

        return response()->json([
            'data' => $attendeeTicket,
        ]);
    }

    /**
     * Check-in / scan a ticket.
     */
    public function update(ScanRequest $request, AttendeeTicket $attendeeTicket)
    {
        $this->authorize('update', $attendeeTicket);

        $isScanned = (bool) $request->validated('is_scanned');

        $attendeeTicket->is_scanned = $isScanned;
        $attendeeTicket->scanned_at = $isScanned ? Carbon::now() : null;
        $attendeeTicket->save();

        return response()->json([
            'data' => $attendeeTicket->refresh(),
        ]);
    }
}
