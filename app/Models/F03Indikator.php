<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class F03Indikator extends Model
{
    use SoftDeletes;

    protected $table = 'f03_indikator';

    protected $fillable = [
        'periode_id',
        'f03_aspek_id',
        'kode',
        'pertanyaan',
        'tipe_jawaban',
        'pilihan_jawaban',
        'urutan',
        'aktif'
    ];

    protected $casts = [
        'pilihan_jawaban' => 'array',
        'aktif' => 'boolean'
    ];

    public $timestamps = true;

    public function aspek()
    {
        return $this->belongsTo(F03Aspek::class, 'f03_aspek_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function jawaban()
    {
        return $this->hasMany(F03Jawaban::class, 'f03_indikator_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('kode', 'asc');
    }

    public function getAverageScoreAttribute()
    {
        return $this->jawaban()->avg('score') ?? 0;
    }
}
