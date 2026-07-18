<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendataanPengisian extends Model
{
    use SoftDeletes;

    protected $table = 'pendataan_pengisian';

    protected $fillable = [
        'periode_id',
        'upp_id',
        'status',
        'dikirim_oleh',
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function upp()
    {
        return $this->belongsTo(Upp::class, 'upp_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function jawaban()
    {
        return $this->hasMany(PendataanJawaban::class, 'pendataan_pengisian_id');
    }
}
