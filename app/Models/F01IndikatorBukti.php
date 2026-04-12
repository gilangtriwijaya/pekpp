<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F01IndikatorBukti extends Model
{
    protected $table = 'f01_indikator_bukti';

    protected $fillable = [
        'f01_indikator_nilai_id', 'jenis', 'nama', 'path_atau_url', 'keterangan'
    ];

    public $timestamps = true;

    public function nilai()
    {
        return $this->belongsTo(F01IndikatorNilai::class, 'f01_indikator_nilai_id');
    }

    public function indikator()
    {
        return $this->nilai->indikator;
    }

    public function pengisian()
    {
        return $this->nilai->f01Pengisian;
    }
}
