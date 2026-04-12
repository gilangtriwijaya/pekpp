<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class F03ResponseDemographic extends Model
{
    protected $table = 'f03_response_demographics';
    
    protected $fillable = [
        'f03_pengisian_id',
        'gender',
        'age',
        'last_education',
        'occupation',
    ];
    
    protected $casts = [
        'age' => 'integer',
    ];
    
    /**
     * Get the pengisian (response) this demographic belongs to
     */
    public function pengisian()
    {
        return $this->belongsTo(F03Pengisian::class, 'f03_pengisian_id');
    }
}
