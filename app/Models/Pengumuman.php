<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'isi',
        'aktif',
        'published_at',
        'expired_at',
        'created_by',
    ];

    protected $casts = [
        'aktif'        => 'boolean',
        'published_at' => 'datetime',
        'expired_at'   => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Relasi ke user yang membuat pengumuman.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: pengumuman yang aktif, sudah dipublish, dan belum kadaluwarsa.
     *
     * Kondisi:
     *   aktif = true
     *   published_at <= now()
     *   expired_at IS NULL OR expired_at >= now()
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query
            ->where('aktif', true)
            ->where('published_at', '<=', now())
            ->where(function (Builder $q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>=', now());
            });
    }
}
