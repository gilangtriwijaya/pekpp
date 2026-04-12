<?php

namespace App\Models;

/**
 * Compatibility wrapper. Prefer using `UserUnitRoleLegacy` directly for
 * migration/export/reconciliation operations.
 *
 * Kept to avoid changing many imports at once.
 */
class UserUnitRole extends UserUnitRoleLegacy
{
    // intentionally empty
}
