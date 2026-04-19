<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Indikator extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'indikator';

    protected $fillable = [
        'aspek_id', 'kode', 'nama', 'deskripsi', 'bukti_dukung', 'urutan', 'aktif'
    ];

    public $timestamps = true;

    public function aspek()
    {
        return $this->belongsTo(Aspek::class, 'aspek_id');
    }

    public function pertanyaan()
    {
        return $this->hasMany(Pertanyaan::class, 'indikator_id');
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', 1);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('aspek_id', 'asc')->orderBy('kode', 'asc');
    }
}
