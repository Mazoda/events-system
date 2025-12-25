<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory, HasTenantScope;
    protected $guarded = [
        'id'
    ];
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
