<?php

namespace App\Policies;

use App\Models\AttendeeTicket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendeeTicketPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Super Admins get full access to manage and view all passes across all tenants.
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
        return $user->isTenantUser() || $user->isTenantAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AttendeeTicket $attendeeTicket): bool
    {
        if ($user->tenant_id !== $attendeeTicket->tenant_id) {
            return false;
        }

        if ($user->isTenantAdmin()) {
            return true;
        }

        if ($user->isTenantUser()) {
            return $user->id === $attendeeTicket->user_id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AttendeeTicket $attendeeTicket): bool|Response
    {
        return ($user->isTenantAdmin() && $user->tenant_id === $attendeeTicket->tenant_id)
            ? Response::allow()
            : Response::deny('Only the Tenant Administrator is authorized to perform check-in operations.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AttendeeTicket $attendeeTicket): bool
    {
        return false;
    }


}
