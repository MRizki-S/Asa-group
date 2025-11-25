<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MasterKprDokumen;
use App\Models\PemesananUnit;
use App\Models\Perumahaan;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;

class ManagePemesananController extends Controller
{
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function index()
    {
        $user         = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();

        // 🔹 Ambil nama perumahaan
        $namaPerumahaan = null;
        if ($perumahaanId) {
            $namaPerumahaan = Perumahaan::where('id', $perumahaanId)->value('nama_perumahaan');
        }

        // ==============================
        // 🔹 PEMESANAN UNIT KPR
        // ==============================
        $pemesananKpr = PemesananUnit::with([
            'customer',
            'sales',
            'unit.blok',
            'kpr.dokumen',
            'kpr.bank',
            'kpr.pemesananUnit',
        ])
            ->where('cara_bayar', 'kpr')
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
        // ⛔ Filter sama juga untuk cash
            ->whereDoesntHave('pengajuanPembatalan', function ($q) {
                $q->where('status_pengajuan', '!=', 'ditolak');
            });

        if ($user->hasRole('Sales') || $user->hasRole('Manager Pemasaran')) {
            $pemesananKpr->where('sales_id', $user->id);
        }

        $pemesananKpr = $pemesananKpr->get();

        // ==============================
        // 🔹 PEMESANAN UNIT CASH
        // ==============================
        $pemesananCash = PemesananUnit::with([
            'customer',
            'sales',
            'unit.blok',
            'cash.dokumen',
            'cash.pemesananUnit',
        ])
            ->where('cara_bayar', 'cash')
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
            ->whereDoesntHave('pengajuanPembatalan', function ($q) {
                $q->where('status_pengajuan', '!=', 'ditolak');
            });

        if ($user->hasRole('Sales')) {
            $pemesananCash->where('sales_id', $user->id);
        }

        $pemesananCash = $pemesananCash->get();

        // ==============================
        // 🔹 Hitung kelengkapan berkas
        // ==============================
        foreach ($pemesananKpr as $item) {
            $item->kelengkapan_berkas = '-';
            $item->total_dokumen      = 0;
            $item->dokumen_lengkap    = 0;

            if ($item->kpr && $item->kpr->bank_id) {
                $bankId = $item->kpr->bank_id;

                $total   = MasterKprDokumen::where('bank_id', $bankId)->count();
                $lengkap = $item->kpr->dokumen->where('status', 1)->count();

                $item->total_dokumen      = $total;
                $item->dokumen_lengkap    = $lengkap;
                $item->kelengkapan_berkas = "{$lengkap} dari {$total}";
            }
        }

        foreach ($pemesananCash as $item) {
            $item->kelengkapan_berkas = '-';
            $item->total_dokumen      = 0;
            $item->dokumen_lengkap    = 0;

            if ($item->cash) {
                $total   = $item->cash->dokumen->count();
                $lengkap = $item->cash->dokumen->where('status', 1)->count();

                $item->total_dokumen      = $total;
                $item->dokumen_lengkap    = $lengkap;
                $item->kelengkapan_berkas = "{$lengkap} dari {$total}";
            }
        }
        // dd($pemesananKpr);
        // ==============================
        // 🔹 Return ke View
        // ==============================
        return view('marketing.manage-pemesanan.index', [
            'pemesananKpr'  => $pemesananKpr,
            'pemesananCash' => $pemesananCash,
            'breadcrumbs'   => [
                [
                    'label' => 'Manage Pemesanan - ' . ($namaPerumahaan ?? '-'),
                    'url'   => route('marketing.managePemesanan.index'),
                ],
            ],
        ]);
    }

    public function rincianTagihan($id)
    {
        // 🔹 Ambil data pemesanan beserta relasi penting
        $pemesanan = PemesananUnit::with([
            'unit',
            'unit.blok',
            'perumahaan',
            'cicilan',
        ])->findOrFail($id);

        // 🔹 Ambil data nama perumahaan dan nama unit
        $namaPerumahaan = $pemesanan->perumahaan->nama_perumahaan ?? '-';
        $namaUnit       = $pemesanan->unit
            ? ($pemesanan->unit->nama_unit ?? $pemesanan->unit->blok->nama_blok ?? '-')
            : '-';

        // 🔹 Ambil semua rincian cicilan (tagihan)
        $rincianTagihan = $pemesanan->cicilan()
            ->where('is_active', 1)
            ->orderBy(column: 'pembayaran_ke')
            ->get();
        // dd($rincianTagihan);
        // 🔹 Return ke view
        return view('marketing.manage-pemesanan.rincian-tagihan.show-rincianTagihan', [
            'pemesanan'      => $pemesanan,
            'rincianTagihan' => $rincianTagihan,
            'breadcrumbs'    => [
                [
                    'label' => 'Manage Pemesanan - ' . $namaPerumahaan,
                    'url'   => route('marketing.managePemesanan.index'),
                ],
                [
                    'label' => 'Rincian Tagihan - ' . $namaUnit,
                    'url'   => '',
                ],
            ],
        ]);
    }

    // print ppjb KPR
    public function exportWordKPR($id)
    {
        // =========================
        // 🔹 Ambil data lengkap dengan relasi
        // =========================
        $pemesanan = PemesananUnit::with([
            'dataDiri',
            'unit.type',
            'perumahaan',
            'kpr',
            'cicilan',
            'promo',
            'keterlambatan',
            'pembatalan',
            'sales',
        ])->findOrFail($id);
        // dd($pemesanan);

        $namaSales   = strtoupper($pemesanan->sales?->nama_lengkap) ?? '-';
        $dataDiri    = $pemesanan->dataDiri;
        $unit        = $pemesanan->unit;
        $type        = $unit?->type;
        $kpr         = $pemesanan->kpr;
        $noPemesanan = $pemesanan->no_pemesanan;

        // =========================
        // 🔹 Format Dasar Data diri pembeli
        // =========================
        $alamatKtp        = $this->formatAlamat($dataDiri);
        $noHpPembeli      = $this->formatNoHp($dataDiri->no_hp ?? '');
        $tanggalPemesanan = $pemesanan->tanggal_pemesanan
            ? $pemesanan->tanggal_pemesanan->translatedFormat('d F Y')
            : '-';
        $hariPemesanan = $pemesanan->tanggal_pemesanan
            ? ucfirst($pemesanan->tanggal_pemesanan->translatedFormat('l'))
            : '-';
        $bulanRomawi = $this->formatBulanRomawi($pemesanan->tanggal_pemesanan);

        // =========================
        // 🔹 Luas Bangunan & Tanah
        // =========================
        $luasBangunan = $type?->luas_bangunan ? rtrim(rtrim(number_format($type->luas_bangunan, 2, '.', ''), '0'), '.') : '-';
        $luasTanah    = $type?->luas_tanah ? rtrim(rtrim(number_format($type->luas_tanah, 2, '.', ''), '0'), '.') : '-';

        // 🔹 Terbilang luas
        $terbilangLuasBangunan = ucfirst($this->terbilang((int) $luasBangunan));
        $terbilangLuasTanah    = ucfirst($this->terbilang((int) $luasTanah));

        // =========================
        // 🔹 Data tambahan KPR
        // =========================
        $hargaTotal          = $kpr->harga_total ?? 0;
        $dpRumahInduk        = $kpr->dp_rumah_induk ?? 0;
        $totalDp             = $kpr->total_dp ?? 0;
        $dpDibayarkanPembeli = $kpr->dp_dibayarkan_pembeli ?? 0;
        $sbumPemerintah      = $kpr->sbum_dari_pemerintah ?? 0;
        $hargaKpr            = $kpr->harga_kpr ?? 0;

        // 🔹 Terbilang harga total
        $terbilangHargaTotal = ucfirst($this->terbilang($hargaTotal)) . ' rupiah';

        // =========================
        // 🔹 Luas & Nominal Kelebihan Tanah
        // =========================
        $luasKelebihanTanah    = $unit?->luas_kelebihan ?? null;
        $nominalKelebihanTanah = $unit?->nominal_kelebihan ?? null;

        // =========================
        // 🔹 Ambil nama-nama
        // =========================
        $namaUnit       = $unit->nama_unit ?? '-';
        $namaPerumahaan = $pemesanan->perumahaan->nama_perumahaan ?? '-';
        $namaType       = $type?->nama_type ?? '-';

        // =========================
        // 🔹 Ambil daftar cicilan
        // =========================
        $cicilanList = $pemesanan->cicilan()
            ->where('is_active', 1)
            ->orderBy('pembayaran_ke')
            ->get(['pembayaran_ke', 'tanggal_jatuh_tempo', 'nominal']);

        // =========================
        // 🔹 Siapkan data untuk cloneRow
        // =========================
        $rows = [];
        foreach ($cicilanList as $i => $c) {
            // Jika ini baris terakhir
            $isLast = $i === count($cicilanList) - 1;

            $rows[] = [
                'NO_PEMBAYARAN'         => $i + 1,
                'KETERANGAN_PEMBAYARAN' => $isLast
                    ? 'Pelunasan'
                    : 'Pembayaran ke-' . $c->pembayaran_ke,
                'TANGGAL_PEMBAYARAN'    => $c->tanggal_jatuh_tempo
                    ? $c->tanggal_jatuh_tempo->translatedFormat('d F Y')
                    : '-',
                'NOMINAL_PEMBAYARAN'    => number_format($c->nominal, 0, ',', '.'),
            ];
        }

        // =========================
        // 🔹 Ambil daftar promo dari snapshot
        // =========================
        $promoList = $pemesanan->promo()->pluck('nama_promo')->toArray();

        // =========================
        // 🔹 Format daftar promo manual (lanjutan dari huruf g. → mulai h.)
        // =========================
        if (count($promoList)) {
            $hurufRange  = range('a', 'z');
            $startLetter = 'h'; // huruf awal lanjutan dari list paten
            $startIndex  = array_search($startLetter, $hurufRange);

            $daftarPromo = '';

            foreach ($promoList as $i => $promo) {
                $huruf = $hurufRange[$startIndex + $i] ?? '?';
                $daftarPromo .= "{$huruf}.    {$promo}\r\n";
            }

        } else {
            $daftarPromo = '';
        }

        // =========================
        // 🔹 Data keterlambatan
        // =========================
        $keterlambatan   = $pemesanan->keterlambatan;
        $persentaseDenda = $keterlambatan?->persentase_denda ?? 0;

        // Hapus ".00" dan ubah ke angka bulat
        $persenBulan = (int) $persentaseDenda;

        // Hitung versi per 6 hari (1/4 bulan)
        $persen6Hari = round($persenBulan / 4, 2);

        // 🔹 Terbilang versi angka bulat (tanpa koma)
        $terbilangKeterlambatanBulan = ucfirst($this->terbilang($persenBulan));
        $terbilangKeterlambatan6Hari = ucfirst($this->terbilang((int) round($persen6Hari)));

        // =========================
        // 🔹 Data pembatalan
        // =========================
        $pembatalan           = $pemesanan->pembatalan;
        $persentasePembatalan = $pembatalan?->persentase_potongan ?? 0;

        // Hapus ".00" dan ubah ke angka bulat
        $pembatalanPersen = (int) $persentasePembatalan;

        // 🔹 Terbilang pembatalan persen
        $terbilangPembatalanPersen = ucfirst($this->terbilang($pembatalanPersen));

        // =========================
        // 🔹 Load Template Word
        // =========================
        $templatePath = public_path('templates/PPJB_KPR_TEMPLATE.docx');
        $template     = new TemplateProcessor($templatePath);

        // 🔁 Isi tabel cicilan
        $template->cloneRowAndSetValues('NO_PEMBAYARAN', $rows);

        // =========================
        // 🔹 Isi ke Template
        // =========================
        $template->setValue('NO_PEMESANAN', $noPemesanan);
        $template->setValue('NAMA_SALES', $namaSales);
        $template->setValue('NAMA_PEMBELI', strtoupper($dataDiri->nama_pribadi ?? '-'));
        $template->setValue('NO_HP_PEMBELI', $noHpPembeli);
        $template->setValue('ALAMAT_KTP', $alamatKtp);
        $template->setValue('NAMA_UNIT', $namaUnit);
        $template->setValue('NAMA_PERUMAHAAN', $namaPerumahaan);
        $template->setValue('TANGGAL_PEMESANAN', $tanggalPemesanan);
        $template->setValue('HARI_PEMESANAN', $hariPemesanan);
        $template->setValue('BULAN_ROMAWI', $bulanRomawi);
        $template->setValue('LUAS_BANGUNAN', $luasBangunan);
        $template->setValue('LUAS_TANAH', $luasTanah);
        $template->setValue('TERBILANG_LUAS_BANGUNAN', $terbilangLuasBangunan);
        $template->setValue('TERBILANG_LUAS_TANAH', $terbilangLuasTanah);
        $template->setValue('NAMA_TIPE', $namaType);
        $template->setValue('HARGA_TOTAL', number_format($hargaTotal, 0, ',', '.'));
        $template->setValue('TERBILANG_HARGA_TOTAL', $terbilangHargaTotal);
        $template->setValue('DP_RUMAH_INDUK', number_format($dpRumahInduk, 0, ',', '.'));
        $template->setValue('TOTAL_DP', number_format($totalDp, 0, ',', '.'));
        $template->setValue('DP_DIBAYARKAN_PEMBELI', number_format($dpDibayarkanPembeli, 0, ',', '.'));
        $template->setValue('SBUM_PEMERINTAH', number_format($sbumPemerintah, 0, ',', '.'));
        $template->setValue('HARGA_KPR', number_format($hargaKpr, 0, ',', '.'));
        $template->setValue('LUAS_KELEBIHAN_TANAH', $luasKelebihanTanah ?? '');
        $template->setValue('NOMINAL_KELEBIHAN_TANAH', $nominalKelebihanTanah ? number_format($nominalKelebihanTanah, 0, ',', '.') : '-');
        $template->setValue('DAFTAR_PROMO', trim($daftarPromo), true);
        $template->setValue('KETERLAMBATAN_BULAN', $persenBulan);
        $template->setValue('KETERLAMBATAN_6HARI', $persen6Hari);
        $template->setValue('TERBILANG_KETERLAMBATAN_BULAN', $terbilangKeterlambatanBulan);
        $template->setValue('TERBILANG_KETERLAMBATAN_6HARI', $terbilangKeterlambatan6Hari);
        $template->setValue('PEMBATALAN_PERSEN', $pembatalanPersen);
        $template->setValue('TERBILANG_PEMBATALAN_PERSEN', $terbilangPembatalanPersen);

        // =========================
        // 🔹 Simpan hasil dan download
        // =========================
        $fileName = 'PPJB_KPR_' . $pemesanan->no_pemesanan . '.docx';
        $tempFile = storage_path('app/public/' . $fileName);
        $template->saveAs($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    // print ppjb CASH
    public function exportWordCASH($id)
    {
        // =========================
        // 🔹 Ambil data lengkap dengan relasi
        // =========================
        $pemesanan = PemesananUnit::with([
            'dataDiri',
            'unit.type',
            'perumahaan',
            'cash',
            'cicilan',
            'promo',
            'keterlambatan',
            'pembatalan',
        ])->findOrFail($id);
        // dd($pemesanan);

        $dataDiri    = $pemesanan->dataDiri;
        $unit        = $pemesanan->unit;
        $type        = $unit?->type;
        $cash        = $pemesanan->cash;
        $noPemesanan = $pemesanan->no_pemesanan;

        // =========================
        // 🔹 Format Dasar Data diri pembeli
        // =========================
        $alamatKtp        = $this->formatAlamat($dataDiri);
        $noHpPembeli      = $this->formatNoHp($dataDiri->no_hp ?? '');
        $tanggalPemesanan = $pemesanan->tanggal_pemesanan
            ? $pemesanan->tanggal_pemesanan->translatedFormat('d F Y')
            : '-';
        $hariPemesanan = $pemesanan->tanggal_pemesanan
            ? ucfirst($pemesanan->tanggal_pemesanan->translatedFormat('l'))
            : '-';
        $bulanRomawi = $this->formatBulanRomawi($pemesanan->tanggal_pemesanan);

        // =========================
        // 🔹 Luas Bangunan & Tanah
        // =========================
        $luasBangunan = $type?->luas_bangunan ? rtrim(rtrim(number_format($type->luas_bangunan, 2, '.', ''), '0'), '.') : '-';
        $luasTanah    = $type?->luas_tanah ? rtrim(rtrim(number_format($type->luas_tanah, 2, '.', ''), '0'), '.') : '-';

        // 🔹 Terbilang luas
        $terbilangLuasBangunan = ucfirst($this->terbilang((int) $luasBangunan));
        $terbilangLuasTanah    = ucfirst($this->terbilang((int) $luasTanah));

        // =========================
        // 🔹 Data Nominal CASH
        // =========================
        $hargaTotal = $cash->harga_jadi ?? 0;
        $hargaRumah = $cash->harga_rumah ?? 0;
        $hargaJadi  = $cash->harga_jadi ?? 0;

        // 🔹 Terbilang harga total
        $terbilangHargaTotal = ucfirst($this->terbilang($hargaTotal)) . ' rupiah';

        // =========================
        // 🔹 Luas & Nominal Kelebihan Tanah
        // =========================
        $luasKelebihanTanah    = $unit?->luas_kelebihan ?? null;
        $nominalKelebihanTanah = $unit?->nominal_kelebihan ?? null;

        // =========================
        // 🔹 Ambil nama-nama
        // =========================
        $namaUnit       = $unit->nama_unit ?? '-';
        $namaPerumahaan = $pemesanan->perumahaan->nama_perumahaan ?? '-';
        $namaType       = $type?->nama_type ?? '-';

        // =========================
        // 🔹 Ambil daftar cicilan
        // =========================
        $cicilanList = $pemesanan->cicilan()
            ->orderBy('pembayaran_ke')
            ->get(['pembayaran_ke', 'tanggal_jatuh_tempo', 'nominal']);

        // =========================
        // 🔹 Siapkan data untuk cloneRow
        // =========================
        $rows = [];
        foreach ($cicilanList as $i => $c) {
            // Jika ini baris terakhir
            $isLast = $i === count($cicilanList) - 1;

            $rows[] = [
                'NO_PEMBAYARAN'         => $i + 1,
                'KETERANGAN_PEMBAYARAN' => $isLast
                    ? 'Pelunasan'
                    : 'Pembayaran ke-' . $c->pembayaran_ke,
                'TANGGAL_PEMBAYARAN'    => $c->tanggal_jatuh_tempo
                    ? $c->tanggal_jatuh_tempo->translatedFormat('d F Y')
                    : '-',
                'NOMINAL_PEMBAYARAN'    => number_format($c->nominal, 0, ',', '.'),
            ];
        }

        // =========================
        // 🔹 Ambil daftar promo dan bonus cash
        // =========================
        $promoList = $pemesanan->promo()->pluck('nama_promo')->toArray();
        $bonusList = $pemesanan->bonusCash()->pluck('nama_bonus')->toArray();
        // dd($bonusList);
        // Gabungkan keduanya
        $combinedList = array_filter(array_merge($promoList, $bonusList));

        // =========================
        // 🔹 Format daftar manual (lanjutan dari huruf g → mulai dari i.)
        // =========================
        if (count($combinedList)) {
            $hurufRange  = range('a', 'z');
            $startLetter = 'i'; // huruf awal lanjutan
            $startIndex  = array_search($startLetter, $hurufRange);

            $daftarPromo = '';
            $indent      = str_repeat(' ', 4); // 4 spasi sebagai tab

            foreach ($combinedList as $i => $item) {
                $huruf = $hurufRange[$startIndex + $i] ?? '?';
                $daftarPromo .= "{$huruf}.{$indent}{$item}\r\n";
            }
        } else {
            $daftarPromo = '';
        }
        // dd($daftarPromo);

        // =========================
        // 🔹 Data keterlambatan
        // =========================
        $keterlambatan   = $pemesanan->keterlambatan;
        $persentaseDenda = $keterlambatan?->persentase_denda ?? 0;

        // Hapus ".00" dan ubah ke angka bulat
        $persenBulan = (int) $persentaseDenda;

        // Hitung versi per 6 hari (1/4 bulan)
        $persen6Hari = round($persenBulan / 4, 2);

        // 🔹 Terbilang versi angka bulat (tanpa koma)
        $terbilangKeterlambatanBulan = ucfirst($this->terbilang($persenBulan));
        $terbilangKeterlambatan6Hari = ucfirst($this->terbilang((int) round($persen6Hari)));

        // =========================
        // 🔹 Data pembatalan
        // =========================
        $pembatalan           = $pemesanan->pembatalan;
        $persentasePembatalan = $pembatalan?->persentase_potongan ?? 0;

        // Hapus ".00" dan ubah ke angka bulat
        $pembatalanPersen = (int) $persentasePembatalan;

        // 🔹 Terbilang pembatalan persen
        $terbilangPembatalanPersen = ucfirst($this->terbilang($pembatalanPersen));

        // =========================
        // 🔹 Load Template Word
        // =========================
        $templatePath = public_path('templates/PPJB_CASH_TEMPLATE.docx');
        $template     = new TemplateProcessor($templatePath);

        // 🔁 Isi tabel cicilan
        $template->cloneRowAndSetValues('NO_PEMBAYARAN', $rows);

        // =========================
        // 🔹 Isi ke Template
        // =========================
        $template->setValue('NO_PEMESANAN', $noPemesanan);
        $template->setValue('NAMA_PEMBELI', strtoupper($dataDiri->nama_pribadi ?? '-'));
        $template->setValue('NO_HP_PEMBELI', $noHpPembeli);
        $template->setValue('ALAMAT_KTP', $alamatKtp);
        $template->setValue('NAMA_UNIT', $namaUnit);
        $template->setValue('NAMA_PERUMAHAAN', $namaPerumahaan);
        $template->setValue('TANGGAL_PEMESANAN', $tanggalPemesanan);
        $template->setValue('HARI_PEMESANAN', $hariPemesanan);
        $template->setValue('BULAN_ROMAWI', $bulanRomawi);
        $template->setValue('LUAS_BANGUNAN', $luasBangunan);
        $template->setValue('LUAS_TANAH', $luasTanah);
        $template->setValue('TERBILANG_LUAS_BANGUNAN', $terbilangLuasBangunan);
        $template->setValue('TERBILANG_LUAS_TANAH', $terbilangLuasTanah);
        $template->setValue('NAMA_TIPE', $namaType);
        $template->setValue('HARGA_TOTAL', number_format($hargaTotal, 0, ',', '.'));
        $template->setValue('TERBILANG_HARGA_TOTAL', $terbilangHargaTotal);
        $template->setValue('HARGA_RUMAH', number_format($hargaRumah, 0, ',', '.'));
        $template->setValue('HARGA_JADI', number_format($hargaJadi, 0, ',', '.'));

        $template->setValue('LUAS_KELEBIHAN_TANAH', $luasKelebihanTanah ?? '');
        $template->setValue('NOMINAL_KELEBIHAN_TANAH', number_format($nominalKelebihanTanah, 0, ',', '.') ?? '-');
        $template->setValue('DAFTAR_PROMO', trim($daftarPromo), true);
        $template->setValue('KETERLAMBATAN_BULAN', $persenBulan);
        $template->setValue('KETERLAMBATAN_6HARI', $persen6Hari);
        $template->setValue('TERBILANG_KETERLAMBATAN_BULAN', $terbilangKeterlambatanBulan);
        $template->setValue('TERBILANG_KETERLAMBATAN_6HARI', $terbilangKeterlambatan6Hari);
        $template->setValue('PEMBATALAN_PERSEN', $pembatalanPersen);
        $template->setValue('TERBILANG_PEMBATALAN_PERSEN', $terbilangPembatalanPersen);

        // =========================
        // 🔹 Simpan hasil dan download
        // =========================
        $fileName = 'PPJB_CASH_' . $pemesanan->no_pemesanan . '.docx';
        $tempFile = storage_path('app/public/' . $fileName);
        $template->saveAs($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    private function formatAlamat($dataDiri)
    {
        if (! $dataDiri) {
            return '-';
        }

        return trim(sprintf(
            '%s, RT/RW %s/%s, DS. %s, KEC. %s, %s, PROV. %s',
            strtoupper($dataDiri->alamat_detail ?? ''),
            $dataDiri->rt ?? '',
            $dataDiri->rw ?? '',
            strtoupper($dataDiri->desa_nama ?? ''),
            strtoupper($dataDiri->kecamatan_nama ?? ''),
            strtoupper($dataDiri->kota_nama ?? ''),
            strtoupper($dataDiri->provinsi_nama ?? '')
        ));
    }

    private function formatNoHp($noHp)
    {
        if (empty($noHp)) {
            return '-';
        }

        $angkaBersih = preg_replace('/\D/', '', $noHp);
        return trim(chunk_split($angkaBersih, 4, '-'), '-');
    }

    private function formatBulanRomawi($tanggal)
    {
        if (! $tanggal) {
            return '-';
        }

        $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        return $romawi[(int) $tanggal->format('m')] ?? '-';
    }
    // =========================
    // 🔢 Konversi angka ke teks (Terbilang)
    private function terbilang($angka)
    {
        $angka = (int) $angka;

        if ($angka === 0) {
            return 'nol';
        }

        $units = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

        if ($angka < 12) {
            return $units[$angka];
        }

        if ($angka < 20) {
            return $this->terbilang($angka - 10) . ' belas';
        }

        if ($angka < 100) {
            $puluhan = (int) floor($angka / 10);
            $sisa    = $angka % 10;
            return trim($this->terbilang($puluhan) . ' puluh ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        if ($angka < 200) {
            return 'seratus ' . $this->terbilang($angka - 100);
        }

        if ($angka < 1000) {
            $ratusan = (int) floor($angka / 100);
            $sisa    = $angka % 100;
            return trim($this->terbilang($ratusan) . ' ratus ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        if ($angka < 2000) {
            return 'seribu ' . $this->terbilang($angka - 1000);
        }

        if ($angka < 1000000) {
            $ribuan = (int) floor($angka / 1000);
            $sisa   = $angka % 1000;
            return trim($this->terbilang($ribuan) . ' ribu ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        if ($angka < 1000000000) { // juta
            $juta = (int) floor($angka / 1000000);
            $sisa = $angka % 1000000;
            return trim($this->terbilang($juta) . ' juta ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        if ($angka < 1000000000000) { // miliar
            $miliar = (int) floor($angka / 1000000000);
            $sisa   = $angka % 1000000000;
            return trim($this->terbilang($miliar) . ' miliar ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        // triliun dan lebih besar
        $triliun = (int) floor($angka / 1000000000000);
        $sisa    = $angka % 1000000000000;
        return trim($this->terbilang($triliun) . ' triliun ' . ($sisa ? $this->terbilang($sisa) : ''));
    }
}
