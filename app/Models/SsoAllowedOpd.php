<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsoAllowedOpd extends Model
{
    protected $table = 'sso_allowed_opds';

    protected $fillable = [
        'user_id', 'app_code', 'opd_id'
    ];

    public $timestamps = true;
}
