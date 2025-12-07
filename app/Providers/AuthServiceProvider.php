<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\AttendeeTicket;


use App\Policies\AttendeeTicketPolicy;
use App\Policies\EventPolicy;
use App\Policies\TenantPolicy;
use App\Policies\TicketTypePolicy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Tenant::class => TenantPolicy::class,
        Event::class => EventPolicy::class,
        TicketType::class => TicketTypePolicy::class,
        AttendeeTicket::class => AttendeeTicketPolicy::class,
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the policies using the Gate facade
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
