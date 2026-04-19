<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aspek extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'aspek';

    protected $fillable = [
        'periode_id', 'kode', 'nama', 'domain', 'urutan', 'bobot', 'aktif', 'keterangan'
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public $timestamps = true;

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function indikator()
    {
        return $this->hasMany(Indikator::class, 'aspek_id');
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('kode', 'asc');
    }
}
