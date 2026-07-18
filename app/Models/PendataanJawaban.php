<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendataanJawaban extends Model
{
    protected $table = 'pendataan_jawaban';

    protected $fillable = [
        'pendataan_pengisian_id',
        'pendataan_pertanyaan_id',
        'nilai',
        'catatan',
        'file_path',
        'file_name'
    ];

    public function pengisian()
    {
        return $this->belongsTo(PendataanPengisian::class, 'pendataan_pengisian_id');
    }

    public function pertanyaan()
    {
        return $this->belongsTo(PendataanPertanyaan::class, 'pendataan_pertanyaan_id');
    }
}
