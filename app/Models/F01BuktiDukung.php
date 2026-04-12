<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F01BuktiDukung extends Model
{
    protected $table = 'f01_bukti_dukung';

    protected $fillable = [
        'f01_pengisian_id', 'indikator_id', 'url_bukti'
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
}
