<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pertanyaan extends Model
{
    use SoftDeletes;
    protected $table = 'pertanyaan';

    protected $fillable = [
        'indikator_id', 'kode', 'label', 'tipe_input', 'opsi_jawaban', 'wajib', 'urutan', 'aktif', 'min', 'max',
        'parent_pertanyaan_id', 'show_when', 'skip_if_answer', 'allow_lainnya'
    ];

    protected $casts = [
        'opsi_jawaban' => 'array',
        'wajib' => 'boolean',
        'aktif' => 'boolean',
        'allow_lainnya' => 'boolean',
    ];

    public $timestamps = true;

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    public function aspek()
    {
        return $this->hasOneThrough(
            Aspek::class,
            Indikator::class,
            'id',           // Foreign key on indikator table
            'id',           // Foreign key on aspek table
            'indikator_id', // Local key on pertanyaan table
            'aspek_id'      // Local key on indikator table
        );
    }

    public function jawaban()
    {
        return $this->hasMany(\App\Models\F01Jawaban::class, 'pertanyaan_id');
    }

    /**
     * Get parent question (if this is a conditional question)
     */
    public function parentQuestion()
    {
        return $this->belongsTo(Pertanyaan::class, 'parent_pertanyaan_id');
    }

    /**
     * Get conditional child questions
     */
    public function conditionalQuestions()
    {
        return $this->hasMany(Pertanyaan::class, 'parent_pertanyaan_id')->orderBy('urutan', 'asc');
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', 1);
    }

    public function scopeOrdered($q)
    {
        // Sort by indikator_id first, then by kode alphanumerically
        return $q->orderBy('indikator_id', 'asc')->orderBy('kode', 'asc');
    }

    // Helper: normalized tipe (text, radio, checkbox, skala)
    public function tipe()
    {
        return $this->tipe_input ?? 'text';
    }

    // Helper: return opsi as array of options (from opsi_jawaban cast)
    // and return a map of value => score when possible
    public function opsi()
    {
        $opts = $this->opsi_jawaban ?? [];
        // if opts are objects like [{label, value, score}], build map value=>score
        $map = [];
        foreach ($opts as $o) {
            if (is_array($o)) {
                $value = $o['value'] ?? ($o['label'] ?? null);
                if ($value === null) continue;
                if (array_key_exists('score', $o)) {
                    $map[$value] = (float) $o['score'];
                } else {
                    // default score 1 for radio options if not specified
                    $map[$value] = 1.0;
                }
            }
        }

        return $map;
    }
}
