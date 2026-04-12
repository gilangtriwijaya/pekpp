<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserUpp extends Model
{
    protected $table = 'user_upp';

    protected $fillable = [
        // Only assignment fields are fillable; provenance fields are guarded
        'user_id', 'upp_id', 'peran', 'aktif'
    ];

    public $timestamps = true;

    protected $casts = [
        'aktif' => 'boolean',
        'ditetapkan_pada' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function upp()
    {
        return $this->belongsTo(Upp::class, 'upp_id');
    }

    public function ditetapkanOleh()
    {
        return $this->belongsTo(User::class, 'ditetapkan_oleh');
    }

    // Peran constants
    public const PERAN_SUPERADMIN = 'superadmin';
    public const PERAN_ADMIN_ORGANISASI = 'admin_organisasi';
    public const PERAN_ADMIN_UPP = 'admin_upp';
    public const PERAN_VERIFIKATOR = 'verifikator';

    /**
     * Boot model and attach validation on saving.
     * Enforces:
     * - allowed `peran` values
     * - `user_id` exists in `users`
     * - `upp_id` exists in `upps`
     * - uniqueness of (user_id, upp_id, peran)
     */
    protected static function booted()
    {
        static::saving(function (self $model) {
            $allowed = [
                self::PERAN_SUPERADMIN,
                self::PERAN_ADMIN_ORGANISASI,
                self::PERAN_ADMIN_UPP,
                self::PERAN_VERIFIKATOR,
            ];

            if (! in_array($model->peran, $allowed, true)) {
                throw new \InvalidArgumentException("Invalid peran: {$model->peran}");
            }

            // Ensure referenced user exists
            if (! \App\Models\User::where('id', $model->user_id)->exists()) {
                throw new \InvalidArgumentException("user_id {$model->user_id} does not exist");
            }

            // Ensure referenced upp exists
            if (! \App\Models\Upp::where('id', $model->upp_id)->exists()) {
                throw new \InvalidArgumentException("upp_id {$model->upp_id} does not exist");
            }

            // Uniqueness check for (user_id, upp_id, peran)
            $q = self::where('user_id', $model->user_id)
                ->where('upp_id', $model->upp_id)
                ->where('peran', $model->peran);

            if ($model->exists) {
                $q->where('id', '!=', $model->id);
            }

            if ($q->exists()) {
                throw new \InvalidArgumentException("Duplicate peran for user_id {$model->user_id} and upp_id {$model->upp_id}");
            }

            // Normalize aktif to boolean
            $model->aktif = (bool) $model->aktif;
        });
    }

    /**
     * Return allowed peran list.
     * Useful for form builders and validations.
     */
    public static function allowedPeran(): array
    {
        return [
            self::PERAN_SUPERADMIN,
            self::PERAN_ADMIN_ORGANISASI,
            self::PERAN_ADMIN_UPP,
            self::PERAN_VERIFIKATOR,
        ];
    }

    // Query scopes
    public function scopeAktif($q)
    {
        return $q->where('aktif', 1);
    }

    public function scopeByUser($q, $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeByUpp($q, $uppId)
    {
        return $q->where('upp_id', $uppId);
    }

    public function scopePeran($q, $peran)
    {
        return $q->where('peran', $peran);
    }
}
