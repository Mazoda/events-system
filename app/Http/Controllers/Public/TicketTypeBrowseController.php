<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Support\Carbon;

class TicketTypeBrowseController extends Controller
{
    public function index(Event $event)
    {
        if (!$event->is_published) {
            abort(404);
        }

        $now = Carbon::now();

        $query = TicketType::query()
            ->where('event_id', $event->id)
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>=', $now);
            })
            ->latest('id');

        return response()->json([
            'data' => $query->paginate(50),
        ]);
    }
}
