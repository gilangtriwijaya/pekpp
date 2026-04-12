<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitF01Request extends FormRequest
{
    public function authorize()
    {
        // Policy checks performed in controller
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'pengisian_id' => 'required|exists:f01_pengisian,id',
        ];
    }
}
