<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class F03Pengisian extends Model
{
    use SoftDeletes;

    protected $table = 'f03_pengisian';

    protected $fillable = [
        'upp_id',
        'periode_id',
        'f03_token_id',
        'response_identifier',
        'ip_address_hashed',
        'browser_fingerprint',
        'response_date',
        'is_duplicate'
    ];

    protected $casts = [
        'is_duplicate' => 'boolean',
        'response_date' => 'datetime'
    ];

    public $timestamps = true;

    public function token()
    {
        return $this->belongsTo(F03Token::class, 'f03_token_id');
    }

    public function upp()
    {
        return $this->belongsTo(Upp::class, 'upp_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function jawaban()
    {
        return $this->hasMany(F03Jawaban::class, 'f03_pengisian_id');
    }

    public function demographic()
    {
        return $this->hasOne(F03ResponseDemographic::class, 'f03_pengisian_id');
    }

    public function getAverageScoreAttribute()
    {
        return $this->jawaban()->avg('score') ?? 0;
    }
}
