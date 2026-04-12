<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F02Skor extends Model
{
    protected $table = 'f02_skors';
    
    protected $fillable = [
        'indikator_id', 'periode_id', 'skor_0', 'skor_1', 'skor_2', 'skor_3', 'skor_4', 'skor_5'
    ];

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    /**
     * Get skor narrative by score number (1-5)
     */
    public function getSkorNarasi($skor)
    {
        $field = 'skor_' . $skor;
        return $this->{$field} ?? null;
    }

    /**
     * Get all skor as array
     */
    public function getAllSdor()
    {
        return [
            0 => $this->skor_0,
            1 => $this->skor_1,
            2 => $this->skor_2,
            3 => $this->skor_3,
            4 => $this->skor_4,
            5 => $this->skor_5
        ];
    }
}
