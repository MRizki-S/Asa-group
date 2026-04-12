<?php

namespace App\Exports;

use App\Models\KpiUser;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KpiUserExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        return view('exports.kpi-excel', [
            'kpi' => KpiUser::with(['user', 'details.tasks'])->findOrFail($this->id)
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Styling tambahan (Border seluruh data)
        return [
            // Bisa menambahkan border atau font styling global di sini jika diperlukan
        ];
    }
}
