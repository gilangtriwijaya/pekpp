<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIndikatorRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user() != null;
    }

    public function rules()
    {
        $aspekId = $this->input('aspek_id') ?? $this->route('indikator')?->aspek_id;

        $rules = [
            'aspek_id' => ['required', 'integer', 'exists:aspek,id'],
            'nama' => ['required', 'string', 'max:500'],
            'kode' => [
                'nullable', 'string', 'max:50',
                Rule::unique('indikator', 'kode')->where('aspek_id', $aspekId),
            ],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'bukti_dukung' => ['nullable', 'string', 'max:2000'],
            'bobot' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'urutan' => ['nullable', 'integer', 'min:1'],
            'aktif' => ['sometimes', 'boolean'],
        ];
        
        // For update, exclude current record from unique check
        if ($this->method() === 'PUT' && $this->route('indikator')) {
            $indikatorId = $this->route('indikator')->id;
            $rules['kode'] = [
                'nullable', 'string', 'max:50',
                Rule::unique('indikator', 'kode')->where('aspek_id', $aspekId)->ignore($indikatorId),
            ];
        }
        
        return $rules;
    }

    public function messages()
    {
        return [
            'aspek_id.required' => 'Pilih Aspek terlebih dahulu',
            'aspek_id.integer' => 'Aspek harus berupa angka',
            'aspek_id.exists' => 'Aspek yang dipilih tidak valid',
            'nama.required' => 'Nama Indikator wajib diisi',
            'nama.string' => 'Nama Indikator harus berupa teks',
            'nama.max' => 'Nama Indikator maksimal 500 karakter',
            'kode.string' => 'Kode harus berupa teks',
            'kode.max' => 'Kode maksimal 50 karakter',
            'kode.unique' => 'Kode Indikator sudah ada di aspek ini pada periode yang sama, gunakan kode yang berbeda',
            'deskripsi.string' => 'Deskripsi harus berupa teks',
            'deskripsi.max' => 'Deskripsi maksimal 2000 karakter',
            'bukti_dukung.string' => 'Bukti Dukung harus berupa teks',
            'bukti_dukung.max' => 'Bukti Dukung maksimal 2000 karakter',
            'bobot.numeric' => 'Bobot harus berupa angka',
            'bobot.min' => 'Bobot minimal 0',
            'bobot.max' => 'Bobot maksimal 100',
            'urutan.integer' => 'Urutan harus berupa angka',
            'urutan.min' => 'Urutan minimal 1',
        ];
    }
}
