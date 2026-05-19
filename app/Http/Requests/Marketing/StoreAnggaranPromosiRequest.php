<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnggaranPromosiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'perumahaan_id' => 'required|exists:perumahaan,id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'quarter' => 'required|in:1,2,3,4',
            'target_anggaran' => 'required|numeric|min:0',
            'realisasi_anggaran' => 'required|numeric|min:0',
            'catatan' => 'nullable|string',
        ];
    }
}
