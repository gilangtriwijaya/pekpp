<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePertanyaanRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user() != null;
    }

    public function rules()
    {
        $rules = [
            'indikator_id' => ['required','integer','exists:indikator,id'],
            'kode' => ['required','string','max:50','unique:pertanyaan,kode'],
            'label' => ['required','string'],
            'tipe_input' => ['required','in:text,textarea,number,radio,checkbox,select,yesno,skala'],
            'opsi_jawaban' => ['nullable','array'],
            'wajib' => ['sometimes','boolean'],
            'urutan' => ['nullable','integer'],
            'aktif' => ['sometimes','boolean'],
            'allow_lainnya' => ['sometimes','boolean'],
            'min' => ['nullable','integer'],
            'max' => ['nullable','integer'],
            'skip_if_answer' => ['nullable','string','max:255'],
        ];
        
        // For update, exclude current record from unique check
        if ($this->method() === 'PUT' && $this->route('pertanyaan')) {
            $rules['kode'] = ['required','string','max:50','unique:pertanyaan,kode,' . $this->route('pertanyaan')->id];
        }
        
        return $rules;
    }

    public function messages()
    {
        return [
            'indikator_id.required' => 'Pilih Indikator terlebih dahulu',
            'indikator_id.integer' => 'Indikator harus berupa angka',
            'indikator_id.exists' => 'Indikator yang dipilih tidak valid',
            'kode.required' => 'Kode Pertanyaan wajib diisi',
            'kode.string' => 'Kode harus berupa teks',
            'kode.max' => 'Kode maksimal 50 karakter',
            'kode.unique' => 'Kode Pertanyaan sudah ada, gunakan kode yang berbeda',
            'label.required' => 'Label Pertanyaan wajib diisi',
            'label.string' => 'Label harus berupa teks',
            'tipe_input.required' => 'Tipe Input wajib dipilih',
            'tipe_input.in' => 'Tipe Input tidak valid',
            'opsi_jawaban.array' => 'Opsi Jawaban harus berupa daftar',
            'wajib.boolean' => 'Status Wajib harus berupa ya/tidak',
            'aktif.boolean' => 'Status Aktif harus berupa ya/tidak',
            'urutan.integer' => 'Urutan harus berupa angka',
            'min.integer' => 'Nilai Minimum harus berupa angka',
            'max.integer' => 'Nilai Maksimum harus berupa angka',
            'skip_if_answer.string' => 'Skip answer harus berupa teks',
            'skip_if_answer.max' => 'Skip answer maksimal 255 karakter',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $data = $this->all();
            $tipe = $data['tipe_input'] ?? null;
            if (in_array($tipe, ['radio','checkbox','select'])) {
                if (empty($data['opsi_jawaban']) || ! is_array($data['opsi_jawaban']) || count($data['opsi_jawaban']) === 0) {
                    $v->errors()->add('opsi_jawaban', 'Opsi jawaban wajib diisi untuk tipe radio/checkbox/select');
                }
            }
            if ($tipe === 'skala') {
                $min = isset($data['min']) ? (int)$data['min'] : null;
                $max = isset($data['max']) ? (int)$data['max'] : null;
                if ($min === null || $max === null || $min >= $max) {
                    $v->errors()->add('min', 'Untuk tipe skala, pastikan Minimum < Maksimum dan keduanya harus diisi');
                }
            }
        });
    }
}
