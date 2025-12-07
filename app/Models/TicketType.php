<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasTenantScope;
    protected $table = 'ticket_types';
    protected $fillable = [
        'tenant_id',
        'event_id',
        'name',
        'description',
        'price',
        'quantity_available',
        'quantity_sold',
        'sale_starts_at',
        'sale_ends_at',
        'is_active',
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'quantity_available' => 'integer',
        'quantity_sold' => 'integer',
        'sale_starts_at' => 'datetime',
        'sale_ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
