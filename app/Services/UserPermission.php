<?php

namespace App\Services;

use App\Models\User;

class UserPermission
{
    /**
     * Get all `user_upp` rows for the user (eager-loaded if possible).
     */
    public function getUserUpps(User $user)
    {
        return $user->getUserUpps();
    }

    /**
     * Check if the user has the given role for the UPP id.
     */
    public function hasRole(User $user, int $uppId, string $peran): bool
    {
        return $user->hasUppRole($uppId, $peran);
    }

    /**
     * Check whether user has any role in a given upp id set.
     */
    public function hasAnyRoleIn(User $user, array $uppIds): bool
    {
        $uppIds = array_map('intval', $uppIds);
        foreach ($user->getUserUpps() as $u) {
            if (in_array((int)$u->upp_id, $uppIds, true) && (bool)$u->aktif) return true;
        }
        return false;
    }
}
