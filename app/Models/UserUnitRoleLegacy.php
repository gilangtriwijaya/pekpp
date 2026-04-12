<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy model for archived `user_unit_roles` data.
 * Use only for migration, export, or reconciliation purposes.
 */
class UserUnitRoleLegacy extends Model
{
    protected $table = 'user_unit_roles_legacy';

    protected $fillable = [
        'user_id', 'opd_unit_id', 'role'
    ];

    public $timestamps = true;
}
