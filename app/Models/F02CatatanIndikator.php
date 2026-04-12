<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F02CatatanIndikator extends Model
{
    protected $table = 'f02_catatan_indikator';

    protected $fillable = [
        'f02_indikator_validasi_id', 'isi', 'dibuat_oleh'
    ];

    public $timestamps = true;

    public function indikatorValidasi()
    {
        return $this->belongsTo(F02IndikatorValidasi::class, 'f02_indikator_validasi_id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
