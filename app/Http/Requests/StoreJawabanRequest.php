<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJawabanRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'pengisian_id' => 'required|exists:f01_pengisian,id',
            'indikator_id' => 'required|integer',
            'pertanyaan_id' => 'required',
            'jawaban' => 'nullable',
        ];
    }
}
