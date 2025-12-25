<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventBrowseController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Event::query()
                ->where('is_published', true)
                ->latest('id')
                ->paginate(50),
        ]);
    }

    public function show(Event $event)
    {
        if (!$event->is_published) {
            abort(404);
        }

        return response()->json([
            'data' => $event,
        ]);
    }
}
