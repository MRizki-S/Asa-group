<?php

namespace App\Http\Controllers\Keuangan;

use App\Models\Jurnal;
use App\Models\AkunKeuangan;
use App\Models\JurnalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PeriodeKeuangan;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TransaksiJurnalController extends Controller
{
    // tambah transaksi jurnal
    public function create()
    {
        $today = Carbon::today();

        // Semua periode
        $periodeKeuangan = PeriodeKeuangan::orderBy('tanggal_mulai')->get();

        // Periode aktif
        $periodeAktif = PeriodeKeuangan::where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();

        // Prefix: JU-YYYYMM
        $prefix = 'JU-' . $today->format(format: 'Ym');

        // Ambil jurnal terakhir di bulan tsb
        $lastJurnal = Jurnal::where('nomor_jurnal', 'like', $prefix . '/%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;

        if ($lastJurnal) {
            // JU-202602/0007 → ambil 0007
            $lastSeq = (int) substr($lastJurnal->nomor_jurnal, -4);
            $nextNumber = $lastSeq + 1;
        }

        // padding 4 digit
        $defaultNomorJurnal = $prefix . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Ambil akun leaf, lalu kelompokkan berdasarkan nama akun induknya
        $akunKeuangan = AkunKeuangan::with('parent') // pastikan ada relasi 'parent' di model
            ->where('is_leaf', true)
            ->orderBy('kode_akun')
            ->get()
            ->groupBy(function ($item) {
                // Kelompokkan berdasarkan nama induk, jika tidak ada pakai 'Tanpa Kategori'
                return $item->parent ? $item->parent->nama_akun : 'Lainnya';
            });

        return view('keuangan.transaksi-jurnal.create', [
            'breadcrumbs' => [
                ['label' => 'Transaksi Jurnal', 'url' => route('keuangan.transaksiJurnal.create')],
            ],
            'periodeKeuangan' => $periodeKeuangan,
            'periodeAktif' => $periodeAktif,
            'defaultNomorJurnal' => $defaultNomorJurnal,
            'akunKeuangan' => $akunKeuangan,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            // HEADER
            'periode_id'    => 'required|exists:periode_keuangan,id',
            'tanggal_jurnal' => 'required|date',
            'nomor_jurnal'  => 'required|unique:jurnal,nomor_jurnal',
            'keterangan'    => 'required|string|max:255',

            // DETAIL
            'items' => 'required|array|min:2',
            'items.*.akun_id' => [
                'required',
                Rule::exists('akun_keuangan', 'id')->where('is_leaf', true),
            ],
            'items.*.debit'  => 'nullable|numeric|min:0',
            'items.*.kredit' => 'nullable|numeric|min:0',
        ]);
        // dd($request->all());

        $totalDebit  = 0;
        $totalKredit = 0;

        foreach ($request->items as $item) {
            $debit  = (float) ($item['debit'] ?? 0);
            $kredit = (float) ($item['kredit'] ?? 0);

            // ❌ dua-duanya diisi
            if ($debit > 0 && $kredit > 0) {
                return back()->withErrors([
                    'items' => 'Satu baris hanya boleh debit ATAU kredit.'
                ])->withInput();
            }

            $totalDebit  += $debit;
            $totalKredit += $kredit;
        }

        if ($totalDebit <= 0 || $totalDebit !== $totalKredit) {
            return back()->withErrors([
                'items' => 'Total debit dan kredit harus seimbang dan lebih dari 0.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            //  JURNAL HEADER
            $jurnal = Jurnal::create([
                'nomor_jurnal' => $request->nomor_jurnal,
                'tanggal'      => $request->tanggal_jurnal,
                'periode_id'   => $request->periode_id,
                'jenis_jurnal' => 'umum', // DISABLE di UI → kunci di backend
                'status'       => 'posted', // atau 'draft' kalau mau approval
                'keterangan'   => $request->keterangan,
                'created_by'   => auth()->id(),
            ]);

            // JURNAL DETAIL
            foreach ($request->items as $item) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id'   => $item['akun_id'],
                    'debit'     => $item['debit'] ?? 0,
                    'kredit'    => $item['kredit'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('keuangan.transaksiJurnal.create')
                ->with('success', 'Jurnal berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan jurnal.'
            ])->withInput();
        }
    }
}
