<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class F01Pengisian extends Model
{
    use SoftDeletes;

    protected $table = 'f01_pengisian';

    protected $fillable = [
        'periode_id', 'upp_id', 'status', 'catatan_umum', 
        'dikirim_oleh', 'dikirim_pada',
        'version_number', 'previous_f01_pengisian_id', 'is_latest_version'
    ];

    public $timestamps = true;

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function upp()
    {
        return $this->belongsTo(Upp::class, 'upp_id');
    }

    public function dikirimOleh()
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function indikatorNilai()
    {
        return $this->hasMany(F01IndikatorNilai::class, 'f01_pengisian_id');
    }

    public function jawaban()
    {
        return $this->hasMany(\App\Models\F01Jawaban::class, 'f01_pengisian_id');
    }

    public function aspekPengisian()
    {
        return $this->hasMany(F01AspekPengisian::class, 'f01_pengisian_id');
    }

    public function f02()
    {
        return $this->hasOne(F02Validasi::class, 'f01_pengisian_id');
    }

    public function buktiDukung()
    {
        return $this->hasMany(F01BuktiDukung::class, 'f01_pengisian_id');
    }

    // NEW: Versioning relationships
    public function previousVersion()
    {
        return $this->belongsTo(F01Pengisian::class, 'previous_f01_pengisian_id');
    }

    public function nextVersion()
    {
        return $this->hasOne(F01Pengisian::class, 'previous_f01_pengisian_id');
    }

    /**
     * Get all versions from v1 to current (entire version chain)
     */
    public function allVersions()
    {
        if (!$this->previous_f01_pengisian_id) {
            // I am v1, get all descendants
            return $this->descendants();
        } else {
            // I'm not v1, find v1 first
            $v1 = $this;
            while ($v1->previous_f01_pengisian_id) {
                $v1 = $v1->previousVersion;
            }
            return $v1->descendants();
        }
    }

    /**
     * Get all versions from this one onwards
     */
    private function descendants()
    {
        $all = [$this];
        $current = $this;

        while ($current->nextVersion) {
            $current = $current->nextVersion;
            $all[] = $current;
        }

        return collect($all);
    }

    public function scopeByPeriode($q, $periodeId)
    {
        return $q->where('periode_id', $periodeId);
    }

    public function scopeByUpp($q, $uppId)
    {
        return $q->where('upp_id', $uppId);
    }

    // NEW: Versioning scopes
    public function scopeLatestVersion($query)
    {
        return $query->where('is_latest_version', true);
    }

    public function scopeByUppAndPeriode($query, $uppId, $periodeId)
    {
        return $query
            ->where('upp_id', $uppId)
            ->where('periode_id', $periodeId)
            ->where('is_latest_version', true);
    }
}