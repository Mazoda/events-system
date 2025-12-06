<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketOrder extends Model
{
    use HasFactory;

    protected $table = 'ticket_orders';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'total_price',
        'quantity_total',
        'transaction_id',
        'status',
        'purchased_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'quantity_total' => 'integer',
        'purchased_at' => 'datetime',
        // Optional: Ensure status is handled as a string enum
        'status' => 'string',
    ];

    // ----------------------------------------------------------------------
    // RELATIONSHIPS
    // ----------------------------------------------------------------------

    /**
     * Get the User who placed the order (the buyer).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Tenant that owns this order record.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }


}