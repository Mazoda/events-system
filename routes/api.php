<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Super\TenantController as SuperTenantController;
use App\Http\Controllers\Super\TenantEventsController;
use App\Http\Controllers\Super\TenantTicketOrdersController;
use App\Http\Controllers\Super\TenantAttendeeTicketsController;
use App\Http\Controllers\Admin\TenantProfileController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\TicketTypeController as AdminTicketTypeController;
use App\Http\Controllers\Admin\TicketOrderController as AdminTicketOrderController;
use App\Http\Controllers\Admin\AttendeeTicketController as AdminAttendeeTicketController;
use App\Http\Controllers\Public\EventBrowseController;
use App\Http\Controllers\Public\TicketTypeBrowseController;
use App\Http\Controllers\User\OrderController as UserOrderController;
use App\Http\Controllers\User\MyTicketController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// -------------------------------------------------------------------------
// Super Admin APIs
// -------------------------------------------------------------------------
Route::prefix('super')
    ->middleware(['auth:sanctum', 'role:super_admin'])
    ->group(function () {
        Route::apiResource('tenants', SuperTenantController::class);
        Route::get('tenants/{tenant}/events', [TenantEventsController::class, 'index']);
        Route::get('tenants/{tenant}/ticket-orders', [TenantTicketOrdersController::class, 'index']);
        Route::get('tenants/{tenant}/attendee-tickets', [TenantAttendeeTicketsController::class, 'index']);
    });

// -------------------------------------------------------------------------
// Tenant Admin APIs
// -------------------------------------------------------------------------
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'tenant', 'role:admin'])
    ->group(function () {
        Route::get('tenant', [TenantProfileController::class, 'show']);
        Route::patch('tenant', [TenantProfileController::class, 'update']);

        Route::apiResource('events', AdminEventController::class);
        Route::apiResource('events.ticket-types', AdminTicketTypeController::class);

        Route::get('ticket-orders', [AdminTicketOrderController::class, 'index']);
        Route::get('ticket-orders/{ticketOrder}', [AdminTicketOrderController::class, 'show']);

        Route::get('attendee-tickets', [AdminAttendeeTicketController::class, 'index']);
        Route::get('attendee-tickets/{attendeeTicket}', [AdminAttendeeTicketController::class, 'show']);
        Route::patch('attendee-tickets/{attendeeTicket}', [AdminAttendeeTicketController::class, 'update']);
    });

// -------------------------------------------------------------------------
// End-user APIs
// -------------------------------------------------------------------------
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::get('events', [EventBrowseController::class, 'index']);
    Route::get('events/{event}', [EventBrowseController::class, 'show']);
    Route::get('events/{event}/ticket-types', [TicketTypeBrowseController::class, 'index']);

    Route::get('my/orders', [UserOrderController::class, 'index']);
    Route::get('my/orders/{ticketOrder}', [UserOrderController::class, 'show']);
    Route::post('orders', [UserOrderController::class, 'store']);

    Route::get('my/tickets', [MyTicketController::class, 'index']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
