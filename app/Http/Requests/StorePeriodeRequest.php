<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodeRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user() != null;
    }

    public function rules()
    {
        return [
            'kode' => ['nullable','string','max:50'],
            'nama' => ['required','string','max:191'],
            'tahun' => ['required','integer','min:2000','max:2100'],
            'tanggal_mulai' => ['nullable','date'],
            'tanggal_selesai' => ['nullable','date','after_or_equal:tanggal_mulai'],
            'status' => ['nullable','in:draft,aktif,ditutup'],
            'keterangan' => ['nullable','string'],
            'target_responden_f03' => ['nullable','integer','min:0'],
            'is_aktif' => ['sometimes','boolean'],
            'status_pengisian' => ['nullable','in:open,locked,closed'],
        ];
    }
}
