<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class F01AspekPengisian extends Model
{
    protected $table = 'f01_aspek_pengisian';

    protected $fillable = [
        'f01_pengisian_id',
        'aspek_id',
        'status',
        'last_saved_at',
    ];

    protected $casts = [
        'last_saved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to F01Pengisian
     */
    public function pengisian(): BelongsTo
    {
        return $this->belongsTo(F01Pengisian::class, 'f01_pengisian_id');
    }

    /**
     * Relationship to Aspek
     */
    public function aspek(): BelongsTo
    {
        return $this->belongsTo(Aspek::class, 'aspek_id');
    }

    /**
     * Get or create aspek pengisian status, defaulting to draft
     */
    public static function getOrCreateStatus($pengisianId, $aspekId, $status = 'draft')
    {
        return self::firstOrCreate(
            [
                'f01_pengisian_id' => $pengisianId,
                'aspek_id' => $aspekId,
            ],
            [
                'status' => $status,
                'last_saved_at' => null,
            ]
        );
    }
}
