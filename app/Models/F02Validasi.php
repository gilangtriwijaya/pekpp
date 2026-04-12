<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F02Validasi extends Model
{
    protected $table = 'f02_validasi';

    protected $fillable = [
        'f01_pengisian_id', 'periode_id', 'status', 'catatan_umum', 'total_nilai', 'nilai_mentah', 'divalidasi_oleh', 'divalidasi_pada', 'updated_by'
    ];

    public $timestamps = true;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SELESAI = 'selesai';

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function f01()
    {
        return $this->belongsTo(F01Pengisian::class, 'f01_pengisian_id');
    }

    public function f01Pengisian()
    {
        return $this->belongsTo(F01Pengisian::class, 'f01_pengisian_id');
    }

    public function upp()
    {
        return $this->hasOneThrough(Upp::class, F01Pengisian::class, 'id', 'id', 'f01_pengisian_id', 'upp_id');
    }

    public function indikatorValidasi()
    {
        return $this->hasMany(F02IndikatorValidasi::class, 'f02_validasi_id');
    }

    public function divalidasiOleh()
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByF01($q, $f01Id)
    {
        return $q->where('f01_pengisian_id', $f01Id);
    }
}
