<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAspekRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() != null;
    }

    public function rules()
    {
        $aspekId = $this->route('aspek')?->id ?? null;
        $periodeId = $this->route('aspek')?->periode_id ?? $this->input('periode_id');

        return [
            'kode' => [
                'nullable', 'string', 'max:50',
                Rule::unique('aspek', 'kode')->where('periode_id', $periodeId)->ignore($aspekId),
            ],
            'nama' => 'required|string|max:255',
            'domain' => 'required|in:internal,publik',
            'bobot' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'kode.unique' => 'Kode Aspek sudah digunakan di periode ini, gunakan kode yang berbeda.',
            'nama.required' => 'Nama aspek harus diisi.',
            'domain.required' => 'Domain harus dipilih.',
            'domain.in' => 'Domain harus salah satu dari: internal atau publik.',
            'bobot.required' => 'Bobot harus diisi.',
            'bobot.numeric' => 'Bobot harus berupa angka.',
            'bobot.min' => 'Bobot minimum adalah 0.',
            'bobot.max' => 'Bobot maksimum adalah 100.',
        ];
    }
}
