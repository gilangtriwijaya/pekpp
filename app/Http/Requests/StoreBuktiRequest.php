<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuktiRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'pengisian_id' => 'required|exists:f01_pengisian,id',
            'indikator_nilai_id' => 'required|exists:f01_indikator_nilai,id',
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ];
    }
}
