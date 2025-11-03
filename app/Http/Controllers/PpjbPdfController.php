<?php
namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\TemplateProcessor;

class PpjbPdfController extends Controller
{
    public function exportPdf($id)
    {
        // Sementara data statik
        $data = [
            'no_ppjb'       => '220.AC-27/PPJB/ADL-ABM/X/2025',
            'tanggal'       => '23 Oktober 2025',
            'pihak_pertama' => [
                'nama'   => 'PT ALVIN BHAKTI MANDIRI',
                'alamat' => 'JL. MERPATI, LINGK. CANGKRING, KEC. PATRANG, KAB. JEMBER',
                'telp'   => '0852-3622-2236',
            ],
            'pihak_kedua'   => [
                'nama'   => 'MOCH. SAID KURZIM',
                'alamat' => 'DSN. BOTOSARI, RT/RW 001/007, DS. DUKUHMENCEK, KEC. SUKORAMBI, KAB. JEMBER',
                'telp'   => '0821-1412-46165',
            ],
        ];

        // Render Blade ke PDF
        $pdf = Pdf::loadView('ppjb.pdf', $data)
            ->setPaper('a4', 'portrait');

        // Download file
        return $pdf->download('PPJB_Cash_' . date('Ymd_His') . '.pdf');
    }

    public function exportWord($id)
    {
        // Path file template Word
        $templatePath = public_path('templates/PPJB_KPR_TEMPLATE.docx');

        // Buat Template Processor
        $template = new TemplateProcessor($templatePath);

        // Isi dinamis data
        $template->setValue('NAMA_PEMBELI', 'MOCH. SAID KURZIM');

        // Simpan hasilnya ke file sementara dan hapus
        $fileName = 'PPJB_Test_' . $id . '.docx';
        $tempFile = storage_path('app/public/' . $fileName);
        $template->saveAs($tempFile);

        // Download hasilnya dan hapus setelah dikirim
        return response()->download($tempFile)->deleteFileAfterSend(true);
    }
}
