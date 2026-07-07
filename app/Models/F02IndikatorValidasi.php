<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F02IndikatorValidasi extends Model
{
    protected $table = 'f02_indikator_validasi';

    protected $fillable = [
        'f02_validasi_id',        'indikator_id',
        'nilai',
        'catatan',
        'status',
        'is_carried_over'
    ];

    protected $casts = [
        'is_carried_over' => 'boolean'
    ];

    public $timestamps = true;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINAL = 'final';

    public function validasi()
    {
        return $this->belongsTo(F02Validasi::class, 'f02_validasi_id');
    }

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    public function catatan()
    {
        return $this->hasMany(F02CatatanIndikator::class, 'f02_indikator_validasi_id');
    }

    public function scopeByValidasi($q, $vId)
    {
        return $q->where('f02_validasi_id', $vId);
    }
}
