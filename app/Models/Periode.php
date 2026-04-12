<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periode extends Model
{
    use SoftDeletes;
    protected $table = 'periode';

    protected $fillable = [
        'kode', 'nama', 'tahun', 'tanggal_mulai', 'tanggal_selesai', 'status', 'keterangan', 'is_aktif', 'target_responden_f03', 'status_pengisian'
    ];

    public $timestamps = true;

    public function aspeks()
    {
        return $this->hasMany(Aspek::class, 'periode_id');
    }

    public function indikator()
    {
        return $this->hasManyThrough(Indikator::class, Aspek::class, 'periode_id', 'aspek_id');
    }

    // F01 relationships
    public function f01Pengiasian()
    {
        return $this->hasMany(F01Pengisian::class, 'periode_id');
    }

    // F02 relationships
    public function f02Validasi()
    {
        return $this->hasMany(F02Validasi::class, 'periode_id');
    }

    // F03 relationships
    public function f03Aspeks()
    {
        return $this->hasMany(F03Aspek::class, 'periode_id');
    }

    public function f03Indikators()
    {
        return $this->hasMany(F03Indikator::class, 'periode_id');
    }

    public function f03Tokens()
    {
        return $this->hasMany(F03Token::class, 'periode_id');
    }

    public function f03Pengisian()
    {
        return $this->hasMany(F03Pengisian::class, 'periode_id');
    }

    // status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_DITUTUP = 'ditutup';

    public function scopeAktif($q)
    {
        return $q->where('is_aktif', 1);
    }

    public function scopeBerjalan($q)
    {
        $today = date('Y-m-d');
        return $q->where('tanggal_mulai', '<=', $today)->where('tanggal_selesai', '>=', $today)->where('status', self::STATUS_AKTIF);
    }
}
