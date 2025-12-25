<?php

namespace App\Services;

use App\Models\AttendeeTicket;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketPurchaseService
{
    /**
     * @param  array<int,array{ticket_type_id:int,quantity:int}>  $items
     */
    public function purchase(User $user, array $items): TicketOrder
    {
        if (!$user->tenant_id) {
            abort(403, 'User is not assigned to a tenant.');
        }

        return DB::transaction(function () use ($user, $items) {
            $now = Carbon::now();

            $typeIds = collect($items)->pluck('ticket_type_id')->unique()->values();

            /** @var \Illuminate\Support\Collection<int,TicketType> $ticketTypes */
            $ticketTypes = TicketType::query()
                ->whereIn('id', $typeIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($ticketTypes->count() !== $typeIds->count()) {
                abort(422, 'One or more ticket types not found.');
            }

            $totalPrice = 0;
            $quantityTotal = 0;

            foreach ($items as $item) {
                $ticketType = $ticketTypes->get($item['ticket_type_id']);
                $quantity = (int) $item['quantity'];

                if (!$ticketType) {
                    abort(422, 'One or more ticket types not found.');
                }

                // Cross-tenant protection
                if ((int) $ticketType->tenant_id !== (int) $user->tenant_id) {
                    abort(403, 'Cross-tenant purchase is not allowed.');
                }

                if (!$ticketType->is_active) {
                    abort(422, 'Ticket type is not active.');
                }

                if ($ticketType->sale_starts_at && $ticketType->sale_starts_at->gt($now)) {
                    abort(422, 'Ticket sales have not started yet.');
                }

                if ($ticketType->sale_ends_at && $ticketType->sale_ends_at->lt($now)) {
                    abort(422, 'Ticket sales have ended.');
                }

                $remaining = (int) $ticketType->quantity_available - (int) $ticketType->quantity_sold;
                if ($remaining < $quantity) {
                    abort(422, 'Not enough inventory for selected ticket type.');
                }

                $ticketType->quantity_sold = (int) $ticketType->quantity_sold + $quantity;
                $ticketType->save();

                $quantityTotal += $quantity;
                $totalPrice += ((float) $ticketType->price) * $quantity;
            }

            $order = TicketOrder::query()->create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'quantity_total' => $quantityTotal,
                'transaction_id' => (string) Str::uuid(),
                'status' => 'paid',
                'purchased_at' => $now,
            ]);

            foreach ($items as $item) {
                $ticketType = $ticketTypes->get($item['ticket_type_id']);
                $quantity = (int) $item['quantity'];

                for ($i = 0; $i < $quantity; $i++) {
                    AttendeeTicket::query()->create([
                        'tenant_id' => $user->tenant_id,
                        'order_id' => $order->id,
                        'ticket_type_id' => $ticketType->id,
                        'user_id' => $user->id,
                        'reference_code' => (string) Str::uuid(),
                        'is_scanned' => false,
                        'scanned_at' => null,
                    ]);
                }
            }

            return $order;
        });
    }
}
