<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class F03Aspek extends Model
{
    use SoftDeletes;

    protected $table = 'f03_aspek';

    protected $fillable = [
        'periode_id',
        'kode',
        'nama',
        'bobot',
        'urutan',
        'aktif',
        'keterangan'
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'bobot' => 'float'
    ];

    public $timestamps = true;

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function indikator()
    {
        return $this->hasMany(F03Indikator::class, 'f03_aspek_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('kode', 'asc');
    }

    public function getAspekScoreAttribute()
    {
        if (!$this->relationLoaded('indikator')) {
            $this->load('indikator');
        }
        
        $jawabanIds = $this->indikator->pluck('id');
        if ($jawabanIds->isEmpty()) {
            return 0;
        }
        
        $score = F03Jawaban::whereIn('f03_indikator_id', $jawabanIds)->avg('score');
        return $score ? round($score, 2) : 0;
    }
}
