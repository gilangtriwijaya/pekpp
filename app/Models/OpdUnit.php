<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpdUnit extends Model
{
    protected $table = 'opd_units';

    protected $fillable = [
        'sso_id', 'opd_id', 'nama', 'created_by', 'updated_by'
    ];

    public $timestamps = true;

    public function opd()
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }
}
