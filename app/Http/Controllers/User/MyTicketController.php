<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendeeTicket;

class MyTicketController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'data' => AttendeeTicket::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->paginate(50),
        ]);
    }
}
