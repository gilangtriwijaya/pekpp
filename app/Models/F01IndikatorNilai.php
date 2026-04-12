<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F01IndikatorNilai extends Model
{
    protected $table = 'f01_indikator_nilai';

    protected $fillable = [
        'f01_pengisian_id', 'indikator_id', 'nilai', 'justifikasi', 'status'
    ];

    public $timestamps = true;

    public function pengisian()
    {
        return $this->belongsTo(F01Pengisian::class, 'f01_pengisian_id');
    }

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    public function bukti()
    {
        // HasMany allows multiple bukti per indikator
        return $this->hasMany(F01IndikatorBukti::class, 'f01_indikator_nilai_id');
    }

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINAL = 'final';

    public function scopeByPengisian($q, $pengisianId)
    {
        return $q->where('f01_pengisian_id', $pengisianId);
    }
}
