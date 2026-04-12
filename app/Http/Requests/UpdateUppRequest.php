<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uppId = $this->route('upp')?->id ?? $this->route('id');

        return [
            'kode' => ['required', 'string', 'max:50', Rule::unique('upps','kode')->ignore($uppId)],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', Rule::in(['opd','unit'])],
            'parent_upp_id' => [
                'nullable',
                'integer',
                'exists:upps,id',
                Rule::requiredIf(fn () => $this->input('jenis') === 'unit'),
            ],
            'opd_id_sso' => ['nullable','integer'],
            'unit_opd_id_sso' => ['nullable','integer', Rule::unique('upps','unit_opd_id_sso')->ignore($uppId)],
            'aktif' => ['boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $jenis = $this->input('jenis');
            $parent = $this->input('parent_upp_id');

            if ($jenis === 'opd' && ! empty($parent)) {
                $v->errors()->add('parent_upp_id', 'When jenis = opd, parent_upp_id must be null.');
            }
            if ($jenis === 'unit' && empty($parent)) {
                $v->errors()->add('parent_upp_id', 'When jenis = unit, parent_upp_id is required.');
            }
        });
    }
}
