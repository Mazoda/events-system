<?php

namespace App\Policies;

use App\Models\TicketType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketTypePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Super Admins automatically get full access to all ticket types across all tenants.
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null; // Continue to the specific method checks below.
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
    public function view(User $user, TicketType $ticketType): bool
    {
        return $user->tenant_id === $ticketType->tenant_id;
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
    public function update(User $user, TicketType $ticketType): bool|Response
    {
        return ($user->isTenantAdmin() && $user->tenant_id === $ticketType->tenant_id)
            ? Response::allow()
            : Response::deny('Only the owning Tenant Admin can modify this ticket inventory.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketType $ticketType): bool|Response
    {
        return ($user->isTenantAdmin() && $user->tenant_id === $ticketType->tenant_id)
            ? Response::allow()
            : Response::deny('Only the owning Tenant Admin can delete this ticket inventory.');
    }


}
