<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        // identity fields writable by mirror or admin UI
        'nama',
        'email',
        'nip',
        'aktif',
        // SSO integration fields
        'sso_user_id',
        'role_sso',
        'last_sync_at',
        // User preferences
        'preferred_upp_ids',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'last_sync_at' => 'datetime',
        'aktif' => 'boolean',
        'preferred_upp_ids' => 'array',
    ];

    // Relation: identity -> multiple app role assignments
    public function userUpps()
    {
        return $this->hasMany(UserUpp::class, 'user_id');
    }

    // Return user_upp rows (eager-load if not loaded)
    public function getUserUpps()
    {
        if (! $this->relationLoaded('userUpps')) {
            $this->load('userUpps.upp');
        }
        return $this->getRelation('userUpps');
    }

    // Convenience: check whether user has a given `peran` for a UPP id
    public function hasUppRole(int $uppId, string $peran): bool
    {
        return $this->getUserUpps()->contains(function ($u) use ($uppId, $peran) {
            return (int)$u->upp_id === (int)$uppId && trim(strtolower($u->peran)) === trim(strtolower($peran)) && (bool)$u->aktif;
        });
    }

    /**
     * Check if user has any active peran globally (across all UPPs) or has global role via SSO
     * Supports multiple role name variations
     */
    public function hasGlobalRole($peranList): bool
    {
        // Normalize to array
        if (!is_array($peranList)) {
            $peranList = [$peranList];
        }
        
        // Normalize all perans to lowercase
        $peranList = array_map(function($p) {
            return strtolower(trim((string)$p));
        }, $peranList);
        
        // First check: SSO role_sso field (for superadmin, admin_organisasi at system level)
        if (!empty($this->role_sso)) {
            $userRole = strtolower(trim((string)$this->role_sso));
            if (in_array($userRole, $peranList)) {
                return true;
            }
        }
        
        // Second check: UserUpp peran (for UPP-level roles)
        return $this->getUserUpps()->contains(function($userUpp) use ($peranList) {
            $userPeran = strtolower(trim((string)$userUpp->peran));
            return in_array($userPeran, $peranList) && (bool)$userUpp->aktif;
        });
    }

    // Simple role helper to be compatible with other apps / packages.
    // Prefers a `role_sso` string column if present; accepts a single role or array of roles.
    public function hasRole($roles): bool
    {
        if (is_array($roles)) {
            foreach ($roles as $r) {
                if ($this->hasRole($r)) return true;
            }
            return false;
        }

        $role = strtolower(trim((string)$roles));
        $current = strtolower(trim($this->role_sso ?? ''));
        return $current !== '' && $current === $role;
    }

    // Alias: check any of the provided roles
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Check if user is system admin (superadmin or org_admin)
     */
    public function isAdmin(): bool
    {
        $adminRoles = ['superadmin', 'org_admin', 'admin_organisasi'];
        return $this->hasRole($adminRoles);
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperadmin(): bool
    {
        return $this->hasRole('superadmin');
    }
}
