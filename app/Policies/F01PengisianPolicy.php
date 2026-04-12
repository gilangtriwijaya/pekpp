<?php

namespace App\Policies;

use App\Models\User;
use App\Models\F01Pengisian;

class F01PengisianPolicy
{
    public function view(User $user, F01Pengisian $pengisian)
    {
        // allow if user is superadmin OR has any active role in that upp
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Check if user has any active role in this upp
        return $user->getUserUpps()->contains(function ($userUpp) use ($pengisian) {
            return (int)$userUpp->upp_id === (int)$pengisian->upp_id && (bool)$userUpp->aktif;
        });
    }

    public function update(User $user, F01Pengisian $pengisian)
    {
        // Allow update during active periode 
        // Superadmin can always update
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Check if user has operator, penyusun, admin_upp, or admin_organisasi role in this upp
        $hasRole = $user->hasUppRole($pengisian->upp_id, 'operator') || 
                   $user->hasUppRole($pengisian->upp_id, 'penyusun') ||
                   $user->hasUppRole($pengisian->upp_id, 'admin_upp') ||
                   $user->hasUppRole($pengisian->upp_id, 'admin_organisasi');
        
        if (!$hasRole) {
            return false;
        }

        // Check if periode is still active (via relationship)
        if ($pengisian->periode && !$pengisian->periode->is_aktif) {
            return false;
        }

        return true;
    }

    public function submit(User $user, F01Pengisian $pengisian)
    {
        return $this->update($user, $pengisian);
    }
}
