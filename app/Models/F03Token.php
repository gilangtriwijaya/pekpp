<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class F03Token extends Model
{
    use SoftDeletes;

    protected $table = 'f03_token';

    protected $fillable = [
        'upp_id',
        'periode_id',
        'token',
        'allow_multiple_responses',
        'expired_date',
        'qr_code',
        'aktif'
    ];

    protected $casts = [
        'allow_multiple_responses' => 'boolean',
        'aktif' => 'boolean',
        'expired_date' => 'datetime'
    ];

    public $timestamps = true;

    public function upp()
    {
        return $this->belongsTo(Upp::class, 'upp_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function pengisian()
    {
        return $this->hasMany(F03Pengisian::class, 'f03_token_id');
    }

    public function isExpired()
    {
        return $this->expired_date && $this->expired_date->isPast();
    }

    public function generateQrCode()
    {
        try {
            $url = route('f03.public.form', ['token' => $this->token]);
            
            // Use Builder pattern with SvgWriter
            $builder = new \Endroid\QrCode\Builder\Builder(
                writer: new \Endroid\QrCode\Writer\SvgWriter(),
                writerOptions: [],
                validateResult: false,
                data: $url,
                encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
                errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Low,
                size: 300,
                margin: 10
            );
            
            $result = $builder->build();
            
            // Get SVG string (not data URI, for better storage and rendering)
            $qrCodeSvg = $result->getString();
            
            // Save to database
            $this->update(['qr_code' => $qrCodeSvg]);
            
            return $qrCodeSvg;
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    // Get QR code - generate if not exists
    public function getQrCodeSvg()
    {
        if (!$this->qr_code) {
            return $this->generateQrCode();
        }
        return $this->qr_code;
    }

    public function getTotalResponseCount()
    {
        return $this->pengisian()->count();
    }
}
