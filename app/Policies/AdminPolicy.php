<?php

namespace App\Policies;

use App\Enums\UserType;
use App\Models\User;

class AdminPolicy
{
    /**
     * Determine if the user can access admin dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return $user->type === UserType::Admin || $user->type === UserType::SuperAdmin;
    }

    /**
     * Determine if user is an admin (admin or super_admin).
     */
    public function isAdmin(User $user): bool
    {
        return $user->type === UserType::Admin || $user->type === UserType::SuperAdmin;
    }

    /**
     * Determine if user is a super admin.
     */
    public function isSuperAdmin(User $user): bool
    {
        return $user->type === UserType::SuperAdmin;
    }
}
