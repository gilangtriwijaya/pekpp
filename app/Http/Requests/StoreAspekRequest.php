<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAspekRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() != null;
    }

    public function rules()
    {
        $periodeId = $this->input('periode_id');

        return [
            'periode_id' => 'required|exists:periode,id',
            'kode' => [
                'nullable', 'string', 'max:50',
                Rule::unique('aspek', 'kode')->where('periode_id', $periodeId),
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
            'periode_id.required' => 'Periode harus dipilih.',
            'periode_id.exists' => 'Periode yang dipilih tidak valid.',
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
