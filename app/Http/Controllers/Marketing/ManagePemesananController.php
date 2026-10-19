<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MasterKprDokumen;
use App\Models\PemesananUnit;
use App\Models\Perumahaan;
use Illuminate\Http\Request;
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

    public function index(Request $request)
    {
        $user = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();

        // ambil nama perumahaan
        $namaPerumahaan = null;
        if ($perumahaanId) {
            $namaPerumahaan = Perumahaan::where('id', $perumahaanId)->value('nama_perumahaan');
        }

        // Filter Tahun dan Bulan berdasarkan tanggal_pemesanan (Default: Tahun ini, Semua Bulan)
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', '');

        // query pemesanan unit kpr (Internal)
        $pemesananKprQuery = PemesananUnit::with([
            'customer',
            'sales',
            'unit.blok',
            'unit.pembangunanUnit.pembangunanUnitQc.pembangunanUnitQcTask',
            'kpr.dokumen',
            'kpr.bank',
            'kpr.pemesananUnit',
        ])
            ->where('cara_bayar', 'kpr')
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
            ->where(function ($q) {
                $q->where('source', 'internal')->orWhereNull('source');
            })
            // ⛔ Filter sama juga untuk cash
            ->whereDoesntHave('pengajuanPembatalan', function ($q) {
                $q->where('status_pengajuan', '!=', 'ditolak');
            });

        if ($tahun) {
            $pemesananKprQuery->whereYear('tanggal_pemesanan', $tahun);
        }
        if ($bulan !== '' && $bulan !== null && $bulan !== 'all') {
            $pemesananKprQuery->whereMonth('tanggal_pemesanan', (int) $bulan);
        }

        if ($user->hasAnyRole(['Marketing', 'STAF PENJUALAN (ADL)', 'STAF PENJUALAN (LHR)'])) {
            $pemesananKprQuery->where('sales_id', $user->id);
        }

        $pemesananKpr = $pemesananKprQuery
            ->orderBy('tanggal_pemesanan', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // query pemesanan unit cash (Internal)
        $pemesananCashQuery = PemesananUnit::with([
            'customer',
            'sales',
            'unit.blok',
            'unit.pembangunanUnit.pembangunanUnitQc.pembangunanUnitQcTask',
            'cash.dokumen',
            'cash.pemesananUnit',
        ])
            ->where('cara_bayar', 'cash')
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
            ->where(function ($q) {
                $q->where('source', 'internal')->orWhereNull('source');
            })
            ->whereDoesntHave('pengajuanPembatalan', function ($q) {
                $q->where('status_pengajuan', '!=', 'ditolak');
            });

        if ($tahun) {
            $pemesananCashQuery->whereYear('tanggal_pemesanan', $tahun);
        }
        if ($bulan !== '' && $bulan !== null && $bulan !== 'all') {
            $pemesananCashQuery->whereMonth('tanggal_pemesanan', (int) $bulan);
        }

        if ($user->hasAnyRole(['Marketing', 'STAF PENJUALAN (ADL)', 'STAF PENJUALAN (LHR)'])) {
            $pemesananCashQuery->where('sales_id', $user->id);
        }

        $pemesananCash = $pemesananCashQuery
            ->orderBy('tanggal_pemesanan', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // hitung kelengkapan berkas dari pemesanan kpr dan cash
        foreach ($pemesananKpr as $item) {
            $item->kelengkapan_berkas = '-';
            $item->total_dokumen = 0;
            $item->dokumen_lengkap = 0;

            if ($item->kpr && $item->kpr->bank_id) {
                $bankId = $item->kpr->bank_id;

                $total = MasterKprDokumen::where('bank_id', $bankId)->count();
                $lengkap = $item->kpr->dokumen->where('status', 1)->count();

                $item->total_dokumen = $total;
                $item->dokumen_lengkap = $lengkap;
                $item->kelengkapan_berkas = "{$lengkap} dari {$total}";
            }
        }

        foreach ($pemesananCash as $item) {
            $item->kelengkapan_berkas = '-';
            $item->total_dokumen = 0;
            $item->dokumen_lengkap = 0;

            if ($item->cash) {
                $total = $item->cash->dokumen->count();
                $lengkap = $item->cash->dokumen->where('status', 1)->count();

                $item->total_dokumen = $total;
                $item->dokumen_lengkap = $lengkap;
                $item->kelengkapan_berkas = "{$lengkap} dari {$total}";
            }
        }

        // return view
        return view('marketing.manage-pemesanan.index', [
            'pemesananKpr' => $pemesananKpr,
            'pemesananCash' => $pemesananCash,
            'namaPerumahaanAktif' => $namaPerumahaan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'breadcrumbs' => [
                [
                    'label' => 'Manage Pemesanan - ' . ($namaPerumahaan ?? '-'),
                    'url' => route('marketing.managePemesanan.index'),
                ],
            ],
        ]);
    }

    public function show($id)
    {
        $pengajuan = PemesananUnit::with([
            'customer',
            'sales',
            'perumahaan',
            'tahap',
            'unit',
            'dataDiri',
            'cash',
            'kpr.bank',
            'caraBayar',
            'cicilan',
            'biayaTambahan',
            'promo',
            'promoAdmin',
            'keterlambatan',
            'pembatalan',
            'bonusCash',
            'bonusKpr',
            'feeAgent.masterAgentFee',
            'agent',
        ])->findOrFail($id);

        return view('marketing.manage-pemesanan.show', [
            'pengajuan'     => $pengajuan,
            'keterlambatan' => $pengajuan->keterlambatan,
            'pembatalan'    => $pengajuan->pembatalan,
            'bonusCash'     => $pengajuan->bonusCash,
            'bonusKpr'      => $pengajuan->bonusKpr,
            'isAgent'       => false,
            'backUrl'       => route('marketing.managePemesanan.index'),
            'pageActive'    => 'ManagePemesanan',
            'breadcrumbs'   => [
                ['label' => 'Kelola Pemesanan', 'url' => route('marketing.managePemesanan.index')],
                ['label' => 'Detail Pemesanan Unit: ' . ($pengajuan->unit->nama_unit ?? '-'), 'url' => '#'],
            ],
        ]);
    }

    public function rincianTagihan($id)
    {
        // query pemesanan unit yang terkair dengan rincian tagihan
        $pemesanan = PemesananUnit::with([
            'unit',
            'unit.blok',
            'perumahaan',
            'cicilan',
        ])->findOrFail($id);

        // Ambil nama perumahaan dan nama unit
        $namaPerumahaan = $pemesanan->perumahaan->nama_perumahaan ?? '-';
        $namaUnit = $pemesanan->unit
            ? ($pemesanan->unit->nama_unit ?? $pemesanan->unit->blok->nama_blok ?? '-')
            : '-';

        // Ambil rincian tagihan
        $rincianTagihan = $pemesanan->cicilan()
            ->where('is_active', 1)
            ->orderBy(column: 'pembayaran_ke')
            ->get();

        // Return ke view
        return view('marketing.manage-pemesanan.rincian-tagihan.show-rincianTagihan', [
            'pemesanan' => $pemesanan,
            'rincianTagihan' => $rincianTagihan,
            'breadcrumbs' => [
                [
                    'label' => 'Manage Pemesanan - ' . $namaPerumahaan,
                    'url' => route('marketing.managePemesanan.index'),
                ],
                [
                    'label' => 'Rincian Tagihan - ' . $namaUnit,
                    'url' => '',
                ],
            ],
        ]);
    }

    // Print PPJB KPR
    public function exportWordKPR($id)
    {
        // Query data pemesanan unit beserta relasi yang dibutuhkan
        $pemesanan = PemesananUnit::with([
            'dataDiri',
            'unit.type',
            'perumahaan',
            'kpr',
            'cicilan',
            'cicilanAdministrasi',
            'promo',
            'promoAdmin',
            'biayaAdministrasi',
            'bonusKpr',
            'keterlambatan',
            'pembatalan',
            'sales',
        ])->findOrFail($id);

        $namaSales = strtoupper($pemesanan->sales?->nama_lengkap ?? '-');
        $dataDiri = $pemesanan->dataDiri;
        $unit = $pemesanan->unit;
        $type = $unit?->type;
        $kpr = $pemesanan->kpr;
        $noPemesanan = $pemesanan->no_pemesanan;

        // Format data diri pembeli sesuai PPJB
        $alamatKtp = $this->formatAlamat($dataDiri);
        $noHpPembeli = $this->formatNoHp($dataDiri->no_hp ?? '');
        $tanggalPemesanan = $pemesanan->tanggal_pemesanan
            ? $pemesanan->tanggal_pemesanan->translatedFormat('d F Y')
            : '-';
        $hariPemesanan = $pemesanan->tanggal_pemesanan
            ? ucfirst($pemesanan->tanggal_pemesanan->translatedFormat('l'))
            : '-';
        $bulanRomawi = $this->formatBulanRomawi($pemesanan->tanggal_pemesanan);

        // Luas dan bangunan
        $luasBangunan = $type?->luas_bangunan ? rtrim(rtrim(number_format($type->luas_bangunan, 2, '.', ''), '0'), '.') : '-';
        $luasTanah = $type?->luas_tanah ? rtrim(rtrim(number_format($type->luas_tanah, 2, '.', ''), '0'), '.') : '-';

        // Terbilang luas bangunan dan tanah
        $terbilangLuasBangunan = ucfirst($this->terbilang((int) $luasBangunan));
        $terbilangLuasTanah = ucfirst($this->terbilang((int) $luasTanah));

        // 1. Data Nominal Pokok Rumah KPR
        $hargaTotal = $kpr->harga_total ?? 0;
        $dpRumah = $kpr->dp_rumah_induk ?? $kpr->dp_dibayarkan_pembeli ?? 0;
        $sbumPemerintah = $kpr->sbum_dari_pemerintah ?: 4000000;
        $totalDp = $dpRumah + $sbumPemerintah; // DP Rumah Induk (DP Rumah + SBUM)
        $dpDibayarkanPembeli = $dpRumah;
        $hargaKpr = $kpr->harga_kpr ?: max(0, $hargaTotal - $sbumPemerintah - $dpRumah);

        // Terbilang harga Pokok
        $terbilangHargaTotal = ucfirst($this->terbilang($hargaTotal)) . ' rupiah';
        $terbilangHargaKpr = ucfirst($this->terbilang($hargaKpr)) . ' rupiah';
        $terbilangDpDibayarkan = ucfirst($this->terbilang($dpDibayarkanPembeli)) . ' rupiah';

        // 2. Data Biaya Administrasi Tambahan KPR
        $totalBiayaAdmin = $kpr->total_biaya_administrasi ?? 0;
        $terbilangBiayaAdmin = ucfirst($this->terbilang($totalBiayaAdmin)) . ' rupiah';
        $catatanBiayaAdmin = $kpr->catatan_biaya_administrasi ?: '-';

        // 3. Grand Total (DP Pembeli + Biaya Admin)
        $grandTotal = $dpDibayarkanPembeli + $totalBiayaAdmin;
        $terbilangGrandTotal = ucfirst($this->terbilang($grandTotal)) . ' rupiah';

        // Luas Kelebihan dan nominal kelebihan tanah
        $luasKelebihanTanah = $unit?->luas_kelebihan ?? null;
        $nominalKelebihanTanah = $unit?->nominal_kelebihan ?? null;

        // Nama Perumahaan, unit, dan type
        $namaUnit = $unit->nama_unit ?? '-';
        $namaPerumahaan = $pemesanan->perumahaan->nama_perumahaan ?? '-';
        $namaType = $type?->nama_type ?? '-';

        // ==========================================
        // 🔹 CICILAN 1: DP RUMAH
        // ==========================================
        $cicilanDpList = $pemesanan->cicilan()
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('tipe_cicilan', 'dp')->orWhereNull('tipe_cicilan');
            })
            ->orderBy('pembayaran_ke')
            ->get(['pembayaran_ke', 'tanggal_jatuh_tempo', 'nominal', 'keterangan']);

        $rowsDp = [];
        if (count($cicilanDpList) > 0) {
            foreach ($cicilanDpList as $i => $c) {
                $isLast = $i === count($cicilanDpList) - 1;
                $ket = $c->keterangan ?: ($isLast ? 'Pelunasan' : 'Pembayaran ke-' . $c->pembayaran_ke);
                $tgl = $c->tanggal_jatuh_tempo ? $c->tanggal_jatuh_tempo->translatedFormat('d F Y') : '-';
                $nom = number_format($c->nominal, 0, ',', '.');

                $rowsDp[] = [
                    'NO_PEMBAYARAN_DP' => $i + 1,
                    'NO_PEMBAYARAN' => $i + 1, // backward compatibility
                    'KETERANGAN_PEMBAYARAN_DP' => $ket,
                    'KETERANGAN_PEMBAYARAN' => $ket,
                    'TANGGAL_PEMBAYARAN_DP' => $tgl,
                    'TANGGAL_PEMBAYARAN' => $tgl,
                    'NOMINAL_PEMBAYARAN_DP' => $nom,
                    'NOMINAL_PEMBAYARAN' => $nom,
                ];
            }
        } else {
            $rowsDp[] = [
                'NO_PEMBAYARAN_DP' => '-',
                'NO_PEMBAYARAN' => '-',
                'KETERANGAN_PEMBAYARAN_DP' => '-',
                'KETERANGAN_PEMBAYARAN' => '-',
                'TANGGAL_PEMBAYARAN_DP' => '-',
                'TANGGAL_PEMBAYARAN' => '-',
                'NOMINAL_PEMBAYARAN_DP' => '0',
                'NOMINAL_PEMBAYARAN' => '0',
            ];
        }

        // ==========================================
        // 🔹 CICILAN 2: BIAYA ADMINISTRASI
        // ==========================================
        $cicilanAdminList = $pemesanan->cicilanAdministrasi()
            ->where('is_active', 1)
            ->orderBy('pembayaran_ke')
            ->get(['pembayaran_ke', 'tanggal_jatuh_tempo', 'nominal', 'keterangan']);

        $rowsAdmin = [];
        $daftarCicilanAdminText = '';
        if (count($cicilanAdminList) > 0) {
            foreach ($cicilanAdminList as $i => $c) {
                $isLast = $i === count($cicilanAdminList) - 1;
                $ket = $c->keterangan ?: ($isLast ? 'Pelunasan' : 'Pembayaran ke-' . $c->pembayaran_ke);
                $tgl = $c->tanggal_jatuh_tempo ? $c->tanggal_jatuh_tempo->translatedFormat('d F Y') : '-';
                $nom = number_format($c->nominal, 0, ',', '.');

                $rowsAdmin[] = [
                    'NO_CICILAN_ADMIN' => $i + 1,
                    'NO_PEMBAYARAN_ADMIN' => $i + 1,
                    'KETERANGAN_CICILAN_ADMIN' => $ket,
                    'KETERANGAN_PEMBAYARAN_ADMIN' => $ket,
                    'TANGGAL_CICILAN_ADMIN' => $tgl,
                    'TANGGAL_PEMBAYARAN_ADMIN' => $tgl,
                    'NOMINAL_CICILAN_ADMIN' => $nom,
                    'NOMINAL_PEMBAYARAN_ADMIN' => $nom,
                ];
                $daftarCicilanAdminText .= ($i + 1) . ". {$ket} ({$tgl}) : Rp {$nom}\r\n";
            }
        } else {
            $rowsAdmin[] = [
                'NO_CICILAN_ADMIN' => '-',
                'NO_PEMBAYARAN_ADMIN' => '-',
                'KETERANGAN_CICILAN_ADMIN' => '-',
                'KETERANGAN_PEMBAYARAN_ADMIN' => '-',
                'TANGGAL_CICILAN_ADMIN' => '-',
                'TANGGAL_PEMBAYARAN_ADMIN' => '-',
                'NOMINAL_CICILAN_ADMIN' => '0',
                'NOMINAL_PEMBAYARAN_ADMIN' => '0',
            ];
            $daftarCicilanAdminText = '-';
        }

        // ==========================================
        // 🔹 RINCIAN ITEM BIAYA ADMINISTRASI
        // ==========================================
        $rincianAdminList = $pemesanan->biayaAdministrasi()->get(['keterangan', 'nominal']);
        $rowsRincianAdmin = [];
        $daftarRincianAdminText = '';
        if (count($rincianAdminList) > 0) {
            foreach ($rincianAdminList as $i => $r) {
                $nom = number_format($r->nominal, 0, ',', '.');
                $rowsRincianAdmin[] = [
                    'NO_ITEM_ADMIN' => $i + 1,
                    'NO_BIAYA_ADMIN' => $i + 1,
                    'NAMA_ITEM_ADMIN' => $r->keterangan,
                    'KETERANGAN_BIAYA_ADMIN' => $r->keterangan,
                    'NOMINAL_ITEM_ADMIN' => $nom,
                    'NOMINAL_BIAYA_ADMIN' => $nom,
                ];
                $daftarRincianAdminText .= ($i + 1) . ". {$r->keterangan}: Rp {$nom}\r\n";
            }
        } else {
            $rowsRincianAdmin[] = [
                'NO_ITEM_ADMIN' => '-',
                'NO_BIAYA_ADMIN' => '-',
                'NAMA_ITEM_ADMIN' => '-',
                'KETERANGAN_BIAYA_ADMIN' => '-',
                'NOMINAL_ITEM_ADMIN' => '0',
                'NOMINAL_BIAYA_ADMIN' => '0',
            ];
            $daftarRincianAdminText = '-';
        }

        // ==========================================
        // 🔹 FASILITAS & LEGALITAS (PROMO & BONUS)
        // ==========================================
        $hurufRange = range('a', 'z');
        $indent = str_repeat(' ', 4);

        // 1. Promo / Fasilitas DP
        $promoDpList = $pemesanan->promo()->pluck('nama_promo')->toArray();
        $daftarPromoDp = '';
        if (count($promoDpList)) {
            foreach ($promoDpList as $i => $item) {
                $huruf = $hurufRange[$i] ?? ($i + 1);
                $daftarPromoDp .= "{$huruf}.{$indent}{$item}\r\n";
            }
        } else {
            $daftarPromoDp = '-';
        }

        // 2. Promo / Fasilitas Biaya Administrasi
        $promoAdminList = $pemesanan->promoAdmin()->pluck('nama_promo')->toArray();
        $daftarPromoAdmin = '';
        if (count($promoAdminList)) {
            foreach ($promoAdminList as $i => $item) {
                $huruf = $hurufRange[$i] ?? ($i + 1);
                $daftarPromoAdmin .= "{$huruf}.{$indent}{$item}\r\n";
            }
        } else {
            $daftarPromoAdmin = '-';
        }

        // 3. Bonus KPR
        $bonusList = $pemesanan->bonusKpr()->whereNotNull('nama_bonus')->where('nama_bonus', '!=', '')->pluck('nama_bonus')->toArray();
        $daftarBonusKpr = '';
        if (count($bonusList)) {
            foreach ($bonusList as $i => $item) {
                $huruf = $hurufRange[$i] ?? ($i + 1);
                $daftarBonusKpr .= "{$huruf}.{$indent}{$item}\r\n";
            }
        } else {
            $daftarBonusKpr = '-';
        }

        // 4. Gabungan Promo DP + Bonus (untuk template lama ${DAFTAR_PROMO})
        $combinedList = array_filter(array_merge($promoDpList, $bonusList));
        $daftarPromoGabungan = '';
        if (count($combinedList)) {
            foreach ($combinedList as $i => $item) {
                $huruf = $hurufRange[$i] ?? ($i + 1);
                $daftarPromoGabungan .= "{$huruf}.{$indent}{$item}\r\n";
            }
        }

        // Data Keterlambatan dan Persentase Denda
        $keterlambatan = $pemesanan->keterlambatan;
        $persentaseDenda = $keterlambatan?->persentase_denda ?? 0;
        $persenBulan = (int) $persentaseDenda;
        $persen6Hari = round($persenBulan / 4, 2);
        $terbilangKeterlambatanBulan = ucfirst($this->terbilang($persenBulan));
        $terbilangKeterlambatan6Hari = ucfirst($this->terbilang((int) round($persen6Hari)));

        // Data Pembatalan
        $pembatalan = $pemesanan->pembatalan;
        $persentasePembatalan = $pembatalan?->persentase_potongan ?? 0;
        $pembatalanPersen = (int) $persentasePembatalan;
        $terbilangPembatalanPersen = ucfirst($this->terbilang($pembatalanPersen));

        $formattedHargaStandar = number_format($hargaTotal, 0, ',', '.');

        // Load template word sesuai dengan perumahaan ADL / LHR
        $templatePath = match ($namaPerumahaan) {
            'Asa Dreamland' => public_path('templates/PPJB/ADL KPR.docx'),
            'Lembah Hijau Residence' => public_path('templates/PPJB/PPJB (KPR) LHR.docx'),
            default => abort(404, 'Template PPJB KPR tidak ditemukan'),
        };
        $template = new TemplateProcessor($templatePath);
        $templateVariables = $template->getVariables();

        // 1. Clone Row untuk Cicilan DP Rumah (Mendukung NO_PEMBAYARAN_DP atau NO_PEMBAYARAN lama)
        if (in_array('NO_PEMBAYARAN_DP', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_PEMBAYARAN_DP', $rowsDp);
        } elseif (in_array('NO_PEMBAYARAN', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_PEMBAYARAN', $rowsDp);
        }

        // 2. Clone Row untuk Cicilan Biaya Administrasi (jika tabel ada di template)
        if (in_array('NO_CICILAN_ADMIN', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_CICILAN_ADMIN', $rowsAdmin);
        } elseif (in_array('NO_PEMBAYARAN_ADMIN', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_PEMBAYARAN_ADMIN', $rowsAdmin);
        }

        // 3. Clone Row untuk Rincian Item Biaya Administrasi (jika tabel ada di template)
        if (in_array('NO_ITEM_ADMIN', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_ITEM_ADMIN', $rowsRincianAdmin);
        } elseif (in_array('NO_BIAYA_ADMIN', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_BIAYA_ADMIN', $rowsRincianAdmin);
        } elseif (in_array('KETERANGAN_BIAYA_ADMIN', $templateVariables)) {
            $template->cloneRowAndSetValues('KETERANGAN_BIAYA_ADMIN', $rowsRincianAdmin);
        } elseif (in_array('NAMA_ITEM_ADMIN', $templateVariables)) {
            $template->cloneRowAndSetValues('NAMA_ITEM_ADMIN', $rowsRincianAdmin);
        }

        // ==========================================
        // 🔹 SET SEMUA VALUE KE TEMPLATE WORD
        // ==========================================
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

        // 1. Data Nominal Pokok Rumah KPR
        $formattedHargaStandar = number_format($hargaTotal, 0, ',', '.');
        $template->setValue('HARGA_TOTAL', $formattedHargaStandar);
        $template->setValue('HARGA_STANDAR', $formattedHargaStandar);
        $template->setValue('HARGA_STANDARD', $formattedHargaStandar);
        $template->setValue('TERBILANG_HARGA_TOTAL', $terbilangHargaTotal);
        $template->setValue('TERBILANG_HARGA_STANDAR', $terbilangHargaTotal);
        $template->setValue('TERBILANG_HARGA_STANDARD', $terbilangHargaTotal);
        $template->setValue('DP_RUMAH_INDUK', number_format($totalDp, 0, ',', '.'));
        $template->setValue('TOTAL_DP', number_format($totalDp, 0, ',', '.'));
        $template->setValue('DP_DIBAYARKAN_PEMBELI', number_format($dpDibayarkanPembeli, 0, ',', '.'));
        $template->setValue('TERBILANG_DP_DIBAYARKAN', $terbilangDpDibayarkan);
        $template->setValue('SBUM_PEMERINTAH', number_format($sbumPemerintah, 0, ',', '.'));
        $template->setValue('HARGA_KPR', number_format($hargaKpr, 0, ',', '.'));
        $template->setValue('TERBILANG_HARGA_KPR', $terbilangHargaKpr);
        $template->setValue('LUAS_KELEBIHAN_TANAH', $luasKelebihanTanah ?? '');
        $template->setValue('NOMINAL_KELEBIHAN_TANAH', $nominalKelebihanTanah ? number_format($nominalKelebihanTanah, 0, ',', '.') : '-');

        // 2. Data Biaya Administrasi Tambahan
        $template->setValue('TOTAL_BIAYA_ADMIN', number_format($totalBiayaAdmin, 0, ',', '.'));
        $template->setValue('TERBILANG_BIAYA_ADMIN', $terbilangBiayaAdmin);
        $template->setValue('CATATAN_BIAYA_ADMIN', $catatanBiayaAdmin);
        $template->setValue('DAFTAR_RINCIAN_ADMIN', trim($daftarRincianAdminText));
        $template->setValue('DAFTAR_CICILAN_ADMIN', trim($daftarCicilanAdminText));

        // 3. Grand Total (Rumah + Admin)
        $template->setValue('GRAND_TOTAL_TAGIHAN', number_format($grandTotal, 0, ',', '.'));
        $template->setValue('TERBILANG_GRAND_TOTAL', $terbilangGrandTotal);

        // 4. Promo & Bonus KPR (Terpisah & Gabungan)
        $template->setValue('DAFTAR_PROMO_DP', trim($daftarPromoDp));
        $template->setValue('DAFTAR_PROMO_ADMIN', trim($daftarPromoAdmin));
        $template->setValue('DAFTAR_BONUS_KPR', trim($daftarBonusKpr));
        $template->setValue('DAFTAR_PROMO', trim($daftarPromoGabungan ?: $daftarPromoDp)); // legacy fallback

        // Ketentuan Keterlambatan & Pembatalan
        $template->setValue('KETERLAMBATAN_BULAN', $persenBulan);
        $template->setValue('KETERLAMBATAN_6HARI', $persen6Hari);
        $template->setValue('TERBILANG_KETERLAMBATAN_BULAN', $terbilangKeterlambatanBulan);
        $template->setValue('TERBILANG_KETERLAMBATAN_6HARI', $terbilangKeterlambatan6Hari);
        $template->setValue('PEMBATALAN_PERSEN', $pembatalanPersen);
        $template->setValue('TERBILANG_PEMBATALAN_PERSEN', $terbilangPembatalanPersen);

        // Simpan hasil dan download
        $suffixPerumahaan = match ($namaPerumahaan) {
            'Asa Dreamland' => 'ADL',
            'Lembah Hijau Residence' => 'LHR',
            default => str_replace(' ', '', $namaPerumahaan),
        };

        $cleanNamaUnit = str_replace(['/', '\\'], '', $namaUnit);
        $fileName = 'PPJB-KPR-' . $cleanNamaUnit . '-' . $suffixPerumahaan . '.docx';

        $tempFile = storage_path('app/public/' . $fileName);
        $template->saveAs($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    // Print PPJB CASH
    public function exportWordCASH($id)
    {
        // Query data pemesanan unit beserta relasi yang dibutuhkan
        $pemesanan = PemesananUnit::with([
            'dataDiri',
            'unit.type',
            'perumahaan',
            'cash',
            'cicilan',
            'promo',
            'spesifikasiCash',
            'biayaTambahan',
            'keterlambatan',
            'pembatalan',
            'sales'
        ])->findOrFail($id);

        $namaSales = strtoupper($pemesanan->sales?->nama_lengkap) ?? '-';
        $dataDiri = $pemesanan->dataDiri;
        $unit = $pemesanan->unit;
        $type = $unit?->type;
        $cash = $pemesanan->cash;
        $noPemesanan = $pemesanan->no_pemesanan;

        // Format data diri pembeli sesuai PPJB
        $alamatKtp = $this->formatAlamat($dataDiri);
        $noHpPembeli = $this->formatNoHp($dataDiri->no_hp ?? '');
        $tanggalPemesanan = $pemesanan->tanggal_pemesanan
            ? $pemesanan->tanggal_pemesanan->translatedFormat('d F Y')
            : '-';
        $hariPemesanan = $pemesanan->tanggal_pemesanan
            ? ucfirst($pemesanan->tanggal_pemesanan->translatedFormat('l'))
            : '-';
        $bulanRomawi = $this->formatBulanRomawi($pemesanan->tanggal_pemesanan);

        // Luas dan bangunan
        $luasBangunan = $type?->luas_bangunan ? rtrim(rtrim(number_format($type->luas_bangunan, 2, '.', ''), '0'), '.') : '-';
        $luasTanah = $type?->luas_tanah ? rtrim(rtrim(number_format($type->luas_tanah, 2, '.', ''), '0'), '.') : '-';

        // Terbilang Luas bangunan dan Tanah
        $terbilangLuasBangunan = ucfirst($this->terbilang((int) $luasBangunan));
        $terbilangLuasTanah = ucfirst($this->terbilang((int) $luasTanah));

        // Data Nominal Tunai/CASH
        $hargaTotal = $cash->harga_jadi ?? 0;
        $hargaRumah = $cash->harga_rumah ?? 0;
        $hargaJadi = $cash->harga_jadi ?? 0;

        // Terbilang Harga Total
        $terbilangHargaTotal = ucfirst($this->terbilang($hargaTotal)) . ' rupiah';

        // Luas Kelebihan dan Nominal Kelebihan Tanah
        $luasKelebihanTanah = $unit?->luas_kelebihan ?? null;
        $nominalKelebihanTanah = $unit?->nominal_kelebihan ?? null;

        // Nama Perumahan, Unit dan Type
        $namaUnit = $unit->nama_unit ?? '-';
        $namaPerumahaan = $pemesanan->perumahaan->nama_perumahaan ?? '-';
        $namaType = $type?->nama_type ?? '-';

        // ==========================================
        // 🔹 BIAYA TAMBAHAN CASH (Spesifikasi Fisik: Bata Merah, dll)
        // ==========================================
        $biayaTambahanList = $pemesanan->spesifikasiCash;
        if (!$biayaTambahanList || $biayaTambahanList->isEmpty()) {
            $biayaTambahanList = $pemesanan->biayaTambahan->where('tipe', 'cash_spesifikasi');
        }
        if ($biayaTambahanList->isEmpty()) {
            $biayaTambahanList = $pemesanan->biayaTambahan;
        }

        $totalBiayaTambahan = $cash->total_tambahan ?? 0;
        if (!$totalBiayaTambahan && $biayaTambahanList->isNotEmpty()) {
            $totalBiayaTambahan = $biayaTambahanList->sum('nominal');
        }
        $terbilangBiayaTambahan = ucfirst($this->terbilang($totalBiayaTambahan)) . ' rupiah';

        $rowsBiayaTambahan = [];
        $daftarBiayaTambahanText = '';
        if ($biayaTambahanList->isNotEmpty()) {
            $tableCounter = 3;
            $textCounter = 1;
            foreach ($biayaTambahanList as $item) {
                if (empty($item->keterangan) && empty($item->nominal)) {
                    continue;
                }
                $nom = number_format($item->nominal, 0, ',', '.');
                $rowsBiayaTambahan[] = [
                    'NO_BIAYA_TAMBAHAN' => $tableCounter,
                    'NO_TAMBAHAN' => $tableCounter,
                    'KETERANGAN_BIAYA_TAMBAHAN' => $item->keterangan,
                    'KETERANGAN_TAMBAHAN' => $item->keterangan,
                    'NOMINAL_BIAYA_TAMBAHAN' => $nom,
                    'NOMINAL_TAMBAHAN' => $nom,
                ];
                $daftarBiayaTambahanText .= "{$textCounter}. {$item->keterangan}: Rp {$nom}\r\n";
                $tableCounter++;
                $textCounter++;
            }
        }

        if (empty($rowsBiayaTambahan)) {
            $rowsBiayaTambahan[] = [
                'NO_BIAYA_TAMBAHAN' => 3,
                'NO_TAMBAHAN' => 3,
                'KETERANGAN_BIAYA_TAMBAHAN' => '-',
                'KETERANGAN_TAMBAHAN' => '-',
                'NOMINAL_BIAYA_TAMBAHAN' => '0',
                'NOMINAL_TAMBAHAN' => '0',
            ];
            $daftarBiayaTambahanText = '-';
        }

        // Ambil Cicilan CASH
        $cicilanList = $pemesanan->cicilan()
            ->orderBy('pembayaran_ke')
            ->get(['pembayaran_ke', 'tanggal_jatuh_tempo', 'nominal']);

        // Data Clone Row untuk cicilan
        $rows = [];
        foreach ($cicilanList as $i => $c) {
            // Jika ini baris terakhir
            $isLast = $i === count($cicilanList) - 1;

            $rows[] = [
                'NO_PEMBAYARAN' => $i + 1,
                'KETERANGAN_PEMBAYARAN' => $isLast
                    ? 'Pelunasan'
                    : 'Pembayaran ke-' . $c->pembayaran_ke,
                'TANGGAL_PEMBAYARAN' => $c->tanggal_jatuh_tempo
                    ? $c->tanggal_jatuh_tempo->translatedFormat('d F Y')
                    : '-',
                'NOMINAL_PEMBAYARAN' => number_format($c->nominal, 0, ',', '.'),
            ];
        }

        // Ambil data promo & bonus cash dari snapshot
        $promoList = $pemesanan->promo()->pluck('nama_promo')->toArray();
        $bonusList = $pemesanan->bonusCash()->pluck('nama_bonus')->toArray();

        // Gabungkan promo + bonus
        $combinedList = array_filter(array_merge($promoList, $bonusList));

        // Format urutan promo/bonus untuk PPJB CASH
        if (count($combinedList)) {

            $hurufRange = range('a', 'z');

            // CASH → selalu mulai dari huruf 'a'
            $startIndex = 0;

            $daftarPromo = '';
            $indent = str_repeat(' ', 4); // 4 spasi sebagai tab

            foreach ($combinedList as $i => $item) {
                $huruf = $hurufRange[$startIndex + $i] ?? '?';
                $daftarPromo .= "{$huruf}.{$indent}{$item}\r\n";
            }
        } else {
            $daftarPromo = '';
        }

        // Data Keterlambatan dan Persentase Denda
        $keterlambatan = $pemesanan->keterlambatan;
        $persentaseDenda = $keterlambatan?->persentase_denda ?? 0;

        // Hapus ".00" dan ubah ke angka bulat
        $persenBulan = (int) $persentaseDenda;

        // Hitung versi per 6 hari (1/4 bulan)
        $persen6Hari = round($persenBulan / 4, 2);

        // Terbilang keterlambatan (tanpa koma)
        $terbilangKeterlambatanBulan = ucfirst($this->terbilang($persenBulan));
        $terbilangKeterlambatan6Hari = ucfirst($this->terbilang((int) round($persen6Hari)));

        // Data Pembatalan
        $pembatalan = $pemesanan->pembatalan;
        $persentasePembatalan = $pembatalan?->persentase_potongan ?? 0;

        // Hapus ".00" dan ubah ke angka bulat
        $pembatalanPersen = (int) $persentasePembatalan;

        // Terbilang Pembatalan Persen
        $terbilangPembatalanPersen = ucfirst($this->terbilang($pembatalanPersen));

        // 🔍 DEBUG: DD SEMUA DATA EXPORT CASH UNTUK DITINJAU USER
        // Load template word sesuai dengan perumahaan ADL / LHR
        $templatePath = match ($namaPerumahaan) {
            'Asa Dreamland' => file_exists(public_path('templates/PPJB/CASH ADL.docx'))
                ? public_path('templates/PPJB/CASH ADL.docx')
                : (file_exists(public_path('templates/PPJB/ADL CASH.docx'))
                    ? public_path('templates/PPJB/ADL CASH.docx')
                    : public_path('templates/PPJB/PPJB (TUNAI) ADL.docx')),
            'Lembah Hijau Residence' => file_exists(public_path('templates/PPJB/PPJB CASH LHR.docx'))
                ? public_path('templates/PPJB/PPJB CASH LHR.docx')
                : (file_exists(public_path('templates/PPJB/LHR CASH.docx'))
                    ? public_path('templates/PPJB/LHR CASH.docx')
                    : public_path('templates/PPJB/PPJB (TUNAI) LHR.docx')),
            default => abort(404, 'Template PPJB TUNAI tidak ditemukan'),
        };
        $template = new TemplateProcessor($templatePath);
        $templateVariables = $template->getVariables();

        // 1. Clone Row untuk Biaya Tambahan (jika tabel ada di template)
        if (in_array('NO_BIAYA_TAMBAHAN', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_BIAYA_TAMBAHAN', $rowsBiayaTambahan);
        } elseif (in_array('NO_TAMBAHAN', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_TAMBAHAN', $rowsBiayaTambahan);
        } elseif (in_array('KETERANGAN_BIAYA_TAMBAHAN', $templateVariables)) {
            $template->cloneRowAndSetValues('KETERANGAN_BIAYA_TAMBAHAN', $rowsBiayaTambahan);
        }

        // 2. Clone Row untuk Cicilan Pembayaran
        if (in_array('NO_PEMBAYARAN', $templateVariables)) {
            $template->cloneRowAndSetValues('NO_PEMBAYARAN', $rows);
        } elseif (in_array('KETERANGAN_PEMBAYARAN', $templateVariables)) {
            $template->cloneRowAndSetValues('KETERANGAN_PEMBAYARAN', $rows);
        }

        // Data dikirim ke word template
        $template->setValue('NO_PEMESANAN', $noPemesanan);
        $template->setValue('NAMA_PEMBELI', strtoupper($dataDiri->nama_pribadi ?? '-'));
        $template->setValue('NAMA_SALES', $namaSales);
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
        $template->setValue('NOMINAL_KELEBIHAN_TANAH', $nominalKelebihanTanah ? number_format($nominalKelebihanTanah, 0, ',', '.') : '-');

        // Biaya Tambahan Cash
        $template->setValue('TOTAL_BIAYA_TAMBAHAN', number_format($totalBiayaTambahan, 0, ',', '.'));
        $template->setValue('TOTAL_TAMBAHAN', number_format($totalBiayaTambahan, 0, ',', '.'));
        $template->setValue('TERBILANG_BIAYA_TAMBAHAN', $terbilangBiayaTambahan);
        $template->setValue('TERBILANG_TOTAL_TAMBAHAN', $terbilangBiayaTambahan);
        $template->setValue('DAFTAR_BIAYA_TAMBAHAN', trim($daftarBiayaTambahanText));

        $template->setValue('DAFTAR_PROMO', trim($daftarPromo), true);
        $template->setValue('KETERLAMBATAN_BULAN', $persenBulan);
        $template->setValue('KETERLAMBATAN_6HARI', $persen6Hari);
        $template->setValue('TERBILANG_KETERLAMBATAN_BULAN', $terbilangKeterlambatanBulan);
        $template->setValue('TERBILANG_KETERLAMBATAN_6HARI', $terbilangKeterlambatan6Hari);
        $template->setValue('PEMBATALAN_PERSEN', $pembatalanPersen);
        $template->setValue('TERBILANG_PEMBATALAN_PERSEN', $terbilangPembatalanPersen);

        // Simpan Hasil dan Download
        $suffixPerumahaan = match ($namaPerumahaan) {
            'Asa Dreamland' => 'ADL',
            'Lembah Hijau Residence' => 'LHR',
            default => str_replace(' ', '', $namaPerumahaan),
        };

        $cleanNamaUnit = str_replace(['/', '\\'], '', $namaUnit);
        $fileName = 'PPJB-CASH-' . $cleanNamaUnit . '-' . $suffixPerumahaan . '.docx';

        $tempFile = storage_path('app/public/' . $fileName);
        $template->saveAs($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    // Private function untuk format alamat sesuai dengan keperluan PPJB
    private function formatAlamat($dataDiri)
    {
        if (!$dataDiri) {
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

    // Private function format No Hp sesuai dengan keperluan PPJB
    private function formatNoHp($noHp)
    {
        if (empty($noHp)) {
            return '-';
        }

        $angkaBersih = preg_replace('/\D/', '', $noHp);
        return trim(chunk_split($angkaBersih, 4, '-'), '-');
    }

    // Private Function Bulan Romawi sesuai dengan keperluan PPJB
    private function formatBulanRomawi($tanggal)
    {
        if (!$tanggal) {
            return '-';
        }

        $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        return $romawi[(int) $tanggal->format('m')] ?? '-';
    }

    // Private Format angka terbilang sesuai dengan keperluan PPJB
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
            $sisa = $angka % 10;
            return trim($this->terbilang($puluhan) . ' puluh ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        if ($angka < 200) {
            return 'seratus ' . $this->terbilang($angka - 100);
        }

        if ($angka < 1000) {
            $ratusan = (int) floor($angka / 100);
            $sisa = $angka % 100;
            return trim($this->terbilang($ratusan) . ' ratus ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        if ($angka < 2000) {
            return 'seribu ' . $this->terbilang($angka - 1000);
        }

        if ($angka < 1000000) {
            $ribuan = (int) floor($angka / 1000);
            $sisa = $angka % 1000;
            return trim($this->terbilang($ribuan) . ' ribu ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        if ($angka < 1000000000) { // juta
            $juta = (int) floor($angka / 1000000);
            $sisa = $angka % 1000000;
            return trim($this->terbilang($juta) . ' juta ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        if ($angka < 1000000000000) { // miliar
            $miliar = (int) floor($angka / 1000000000);
            $sisa = $angka % 1000000000;
            return trim($this->terbilang($miliar) . ' miliar ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        // triliun dan lebih besar
        $triliun = (int) floor($angka / 1000000000000);
        $sisa = $angka % 1000000000000;
        return trim($this->terbilang($triliun) . ' triliun ' . ($sisa ? $this->terbilang($sisa) : ''));
    }

    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', '');
        $source = $request->input('source', 'internal');

        $perumahaanId = $this->currentPerumahaanId();
        $namaPerumahaan = null;
        if ($perumahaanId) {
            $namaPerumahaan = Perumahaan::where('id', $perumahaanId)->value('nama_perumahaan');
        }

        $namaBulan = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $periode = '(' . $tahun . ')';
        if ($bulan !== '' && $bulan !== null && $bulan !== 'all') {
            $bulanName = $namaBulan[(int) $bulan] ?? $bulan;
            $periode = '(' . $bulanName . ' ' . $tahun . ')';
        }

        $prefix = match ($source) {
            'agent' => 'Data-Closing-Unit-Agent',
            'all'   => 'Data-Closing-Unit-All',
            default => 'Data-Closing-Unit',
        };

        $perumahaanSuffix = $namaPerumahaan ? '-' . $namaPerumahaan : '';
        $fileName = $prefix . $perumahaanSuffix . ' ' . $periode . '.xlsx';

        // Check if custom columns are selected
        $columns = $request->input('columns', []);
        if (is_string($columns)) {
            $columns = explode(',', $columns);
        }
        $columns = array_filter(array_map('trim', $columns));

        if (!empty($columns)) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\CustomClosingUnitExport($tahun, $bulan, $perumahaanId, $namaPerumahaan, $columns, $source),
                $fileName
            );
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DataClosingUnitExport($tahun, $bulan, $perumahaanId, $namaPerumahaan, $source),
            $fileName
        );
    }
}
