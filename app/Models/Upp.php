<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upp extends Model
{
    use SoftDeletes;

    protected $table = 'upps';

    protected $fillable = [
        // UI-editable fields only; protect SSO/system/audit fields
        'code', 'kode', 'nama', 'slug', 'jenis', 'parent_upp_id',
        'alamat', 'telepon', 'status', 'aktif',
        'opd_id_sso', 'unit_opd_id_sso',
    ];

    public $timestamps = true;

    public function parent()
    {
        return $this->belongsTo(Upp::class, 'parent_upp_id');
    }

    public function children()
    {
        return $this->hasMany(Upp::class, 'parent_upp_id');
    }

    // Reference to local OPD (mapped by SSO id)
    public function opd()
    {
        return $this->belongsTo(Opd::class, 'opd_id_sso', 'sso_id');
    }

    // Reference to local OPD unit (mapped by SSO id)
    public function opdUnit()
    {
        return $this->belongsTo(OpdUnit::class, 'unit_opd_id_sso', 'sso_id');
    }

    public function userUpps()
    {
        return $this->hasMany(UserUpp::class, 'upp_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // F03 relationships
    public function f03Tokens()
    {
        return $this->hasMany(F03Token::class, 'upp_id');
    }

    public function f03Pengisians()
    {
        return $this->hasMany(F03Pengisian::class, 'upp_id');
    }

    // status constants
    public const STATUS_AKTIF = 'AKTIF';
    public const STATUS_NONAKTIF = 'NONAKTIF';

    // Scopes
    public function scopeAktif($q)
    {
        return $q->where('status', self::STATUS_AKTIF);
    }

    public function scopeOpd($q)
    {
        return $q->whereNotNull('opd_id_sso')->whereNull('unit_opd_id_sso');
    }

    public function scopeUnit($q)
    {
        return $q->whereNotNull('unit_opd_id_sso');
    }
}
