<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PendataanPertanyaan extends Model
{
    use SoftDeletes;

    protected $table = 'pendataan_pertanyaan';

    protected $fillable = [
        'pendataan_aspek_id',
        'kode',
        'label',
        'tipe_input',
        'opsi_jawaban',
        'wajib',
        'urutan',
        'aktif'
    ];

    protected $casts = [
        'opsi_jawaban' => 'array',
        'wajib' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function aspek()
    {
        return $this->belongsTo(PendataanAspek::class, 'pendataan_aspek_id');
    }

    public function jawaban()
    {
        return $this->hasMany(PendataanJawaban::class, 'pendataan_pertanyaan_id');
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', 1);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('urutan', 'asc')->orderBy('kode', 'asc');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode)) {
                $model->kode = 'P' . strtoupper(Str::random(8));
            }
        });
    }

    public function tipe()
    {
        return $this->tipe_input ?? 'text';
    }
}
