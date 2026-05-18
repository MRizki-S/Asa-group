<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

class StoreTargetPenjualanRequest extends FormRequest
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
            'target_penjualan_quarter' => 'required|integer|min:0',
            'monthly_targets' => 'required|array|size:3',
            'monthly_targets.*.bulan' => 'required|integer|min:1|max:12',
            'monthly_targets.*.target' => 'required|integer|min:0',
        ];
    }
}
