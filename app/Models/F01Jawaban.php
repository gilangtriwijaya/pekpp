<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F01Jawaban extends Model
{
    protected $table = 'f01_jawaban';

    protected $fillable = [
        'f01_pengisian_id', 'pertanyaan_id', 'nilai'
    ];

    protected $casts = [
        'nilai' => 'json'
    ];

    public function pengisian()
    {
        return $this->belongsTo(F01Pengisian::class, 'f01_pengisian_id');
    }

    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class, 'pertanyaan_id');
    }
}
