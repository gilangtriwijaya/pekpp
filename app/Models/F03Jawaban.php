<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F03Jawaban extends Model
{
    protected $table = 'f03_jawaban';

    protected $fillable = [
        'f03_pengisian_id',
        'f03_indikator_id',
        'score',
        'catatan',
        'response_text'
    ];

    protected $casts = [
        'score' => 'integer'
    ];

    public $timestamps = true;

    public function pengisian()
    {
        return $this->belongsTo(F03Pengisian::class, 'f03_pengisian_id');
    }

    public function indikator()
    {
        return $this->belongsTo(F03Indikator::class, 'f03_indikator_id');
    }
}
