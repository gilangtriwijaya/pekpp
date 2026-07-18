<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendataanAspek extends Model
{
    use SoftDeletes;

    protected $table = 'pendataan_aspek';

    protected $fillable = [
        'periode_id',
        'kode',
        'nama',
        'urutan',
        'aktif',
        'keterangan'
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function pertanyaan()
    {
        return $this->hasMany(PendataanPertanyaan::class, 'pendataan_aspek_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('kode', 'asc');
    }
}
