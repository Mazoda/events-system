<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendeeTicket extends Model
{
    use HasFactory, HasTenantScope;

    protected $table = 'attendee_tickets';

    protected $fillable = [
        'tenant_id',
        'order_id',
        'ticket_type_id',
        'user_id',
        'reference_code',
        'is_scanned',
        'scanned_at',
    ];

    protected $casts = [
        'is_scanned' => 'boolean',
        'scanned_at' => 'datetime',
    ];
    // `id` is the primary key; `reference_code` is the public UUID lookup key.

    // ----------------------------------------------------------------------
    // RELATIONSHIPS
    // ----------------------------------------------------------------------

    /**
     * Get the parent order (transaction) this pass belongs to.
     */
    public function order()
    {
        return $this->belongsTo(TicketOrder::class);
    }

    /**
     * Get the type/tier (inventory definition) this pass came from.
     */
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * Get the actual user assigned to this specific pass.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}