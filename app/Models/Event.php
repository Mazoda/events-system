<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasTenantScope;
    protected $guarded = [
        'id'
    ];
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
