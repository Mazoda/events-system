<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Super Admins can perform any action on any event.
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Continue to the specific method checks below.
        return null;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTenantAdmin() || $user->isTenantUser();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        return $user->tenant_id === $event->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isTenantAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): Response|bool
    {
        return ($user->isTenantAdmin() && $user->tenant_id === $event->tenant_id)
            ? Response::allow()
            : Response::deny('You do not have permission to update this event.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): Response|bool
    {
        // Must be an Admin AND the event must belong to their tenant.
        return ($user->isTenantAdmin() && $user->tenant_id === $event->tenant_id)
            ? Response::allow()
            : Response::deny('You do not have permission to delete this event.');
    }

}
