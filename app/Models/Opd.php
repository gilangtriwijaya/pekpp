<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opd extends Model
{
    protected $table = 'opds';

    protected $fillable = [
        'sso_id', 'nama', 'slug', 'pimpinan', 'alamat', 'telepon',
        'ttd_file_path', 'nip', 'pangkat', 'golongan', 'created_by', 'updated_by',
    ];

    public $timestamps = true;
}
