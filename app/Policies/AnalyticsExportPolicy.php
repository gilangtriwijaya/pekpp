<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AnalyticsExport;

class AnalyticsExportPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole('admin') || $user->hasRole('analyst');
    }

    public function create(User $user)
    {
        return $user->hasRole('admin') || $user->hasRole('analyst');
    }

    public function download(User $user, AnalyticsExport $export)
    {
        // allow admins and same-tenant users
        if ($user->hasRole('admin')) return true;
        return $user->tenant_id === $export->tenant_id;
    }
}
