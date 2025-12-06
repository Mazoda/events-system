<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $guarded = [
        'id'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
