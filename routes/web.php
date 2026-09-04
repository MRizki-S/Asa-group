<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard\MarketingDashboardController;
use App\Http\Controllers\Etalase\BlokController;
use App\Http\Controllers\Etalase\EtalaseJsonController;
use App\Http\Controllers\Etalase\KualifikasiBlokController;
use App\Http\Controllers\Etalase\PerubahaanHargaTypeUnitController;
use App\Http\Controllers\Etalase\PerumahaanController;
use App\Http\Controllers\Etalase\TahapController;
use App\Http\Controllers\Etalase\TahapKualifikasiController;
use App\Http\Controllers\Etalase\TahapTypeController;
use App\Http\Controllers\Etalase\TypeController;
use App\Http\Controllers\Etalase\UnitController;
use App\Http\Controllers\FeeAgenController;
use App\Http\Controllers\Gudang\AuditLogStockController;
use App\Http\Controllers\Gudang\BarangRusakController;
use App\Http\Controllers\Gudang\DaftarNotaMasukController;
use App\Http\Controllers\Gudang\DraftNotaMasukController;
use App\Http\Controllers\Gudang\KomposisiRakitanController;
use App\Http\Controllers\Gudang\MasterBarangController;
use App\Http\Controllers\Gudang\MasterSatuanBarangController;
use App\Http\Controllers\Gudang\MasterSupplierController;
use App\Http\Controllers\Gudang\MasterTukangController;
use App\Http\Controllers\Gudang\NotaBarangMasukController;
use App\Http\Controllers\Gudang\PengajuanUpahHarianTukangController;
use App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangController;
use App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanUnitController;
use App\Http\Controllers\Gudang\ProduksiRakitanController;
use App\Http\Controllers\Gudang\RekapNotaMasukController;
use App\Http\Controllers\Gudang\StockBarangController;
use App\Http\Controllers\Gudang\TransferPenyesuainStockController;
use App\Http\Controllers\Gudang\TransferStockBarangController;
use App\Http\Controllers\Keuangan\AkunKeuanganController;
use App\Http\Controllers\Keuangan\BukuBesarController;
use App\Http\Controllers\Keuangan\DaftarUpahHarianTukangKeuanganController;
use App\Http\Controllers\Keuangan\KategoriAkunKeuanganController;
use App\Http\Controllers\Keuangan\LaporanJurnalController;
use App\Http\Controllers\Keuangan\NeracaSaldoController;
use App\Http\Controllers\Keuangan\PeriodeKeuanganController;
use App\Http\Controllers\Keuangan\RiwayatUpahHarianTukangController;
use App\Http\Controllers\Keuangan\TransaksiJurnalController;
use App\Http\Controllers\Kpi\KpiDashboardController;
use App\Http\Controllers\Kpi\KpiExportController;
use App\Http\Controllers\Kpi\KpiKomponenController;
use App\Http\Controllers\Kpi\KpiReviewController;
use App\Http\Controllers\Kpi\KpiUserController;
use App\Http\Controllers\Marketing\AdendumController;
use App\Http\Controllers\Marketing\AdendumListController;
use App\Http\Controllers\Marketing\AgenController;
use App\Http\Controllers\Marketing\AkunUserController;
use App\Http\Controllers\Marketing\AnggaranPromosiController;
use App\Http\Controllers\Marketing\GantiUnitController;
use App\Http\Controllers\Marketing\KelengkapanBerkasCashController;
use App\Http\Controllers\Marketing\KelengkapanBerkasKprController;
use App\Http\Controllers\Marketing\ManagePemesananAgentController;
use App\Http\Controllers\Marketing\ManagePemesananController;
use App\Http\Controllers\Marketing\PemesananUnitController;
use App\Http\Controllers\Marketing\PengajuanPembatalanController;
use App\Http\Controllers\Marketing\PengajuanPemesananController;
use App\Http\Controllers\Marketing\PindahUnitController;
use App\Http\Controllers\Marketing\SettingBonusCashController;
use App\Http\Controllers\Marketing\SettingBonusKprController;
use App\Http\Controllers\Marketing\SettingCaraBayarController;
use App\Http\Controllers\Marketing\SettingKeterlambatanController;
use App\Http\Controllers\Marketing\SettingMutuPpjbController;
use App\Http\Controllers\Marketing\SettingPembatalanController;
use App\Http\Controllers\Marketing\SettingPpjbController;
use App\Http\Controllers\Marketing\SettingPpjbJsonController;
use App\Http\Controllers\Marketing\SettingPromoPpjbController;
use App\Http\Controllers\Marketing\TargetPenjualanController;
use App\Http\Controllers\PerumahaanSelectController;
use App\Http\Controllers\Produksi\KonfirmasiPembangunanController;
use App\Http\Controllers\Produksi\MasterQcRapController;
use App\Http\Controllers\Produksi\PembangunanKawasan\BuatPembangunanKawasanController;
use App\Http\Controllers\Produksi\PembangunanKawasan\PembangunanKawasanController;
use App\Http\Controllers\Produksi\PembangunanProyek\BuatPembangunanProyekController;
use App\Http\Controllers\Produksi\PembangunanProyek\PembangunanProyekController;
use App\Http\Controllers\Produksi\PembangunanUnit\PembangunanUnitBarangReturnController;
use App\Http\Controllers\Produksi\PembangunanUnit\PembangunanUnitController;
use App\Http\Controllers\Produksi\PembangunanUnit\PembangunanUnitOrderBarangController;
use App\Http\Controllers\Produksi\PembangunanUnit\PembangunanUnitPengajuanUpahController;
use App\Http\Controllers\Produksi\PenamaanUpahController;
use App\Http\Controllers\Produksi\PermintaanDibangunController;
use App\Http\Controllers\Produksi\PersetujuanUpahController;
use App\Http\Controllers\Produksi\PersetujuanUpahKawasanController;
use App\Http\Controllers\Produksi\PersetujuanUpahKontraktorController;
use App\Http\Controllers\Produksi\PersetujuanUpahPropertiController;
use App\Http\Controllers\Produksi\TerminController;
use App\Http\Controllers\Superadmin\AkunKaryawanController;
use App\Http\Controllers\Superadmin\DevisiController;
use App\Http\Controllers\Superadmin\KaryawanController;
use App\Http\Controllers\Superadmin\RoleHakAksesController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// API Wilayah Proxy
Route::prefix('api/wilayah')->group(function () {
    Route::get('/provinsi', function () {
        return Http::get('https://wilayah.id/api/provinces.json')->json();
    });

    Route::get('/kota/{provinsiCode}', function ($provinsiCode) {
        return Http::get("https://wilayah.id/api/regencies/{$provinsiCode}.json")->json();
    });

    Route::get('/kecamatan/{kotaCode}', function ($kotaCode) {
        return Http::get("https://wilayah.id/api/districts/{$kotaCode}.json")->json();
    });

    Route::get('/desa/{kecamatanCode}', function ($kecamatanCode) {
        return Http::get("https://wilayah.id/api/villages/{$kecamatanCode}.json")->json();
    });
});

Route::get('/', function () {
    // dd(session()->all());
    return view('dashboard.Welcome');
})->middleware('auth');

// fitur dalam pengembangan
Route::get('/under-development', function () {
    return view('pages.under-development');
})->name('under-development');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

Route::get('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/pilih-perumahaan', [PerumahaanSelectController::class, 'index'])
        ->name(name: 'perumahaan.select');

    Route::post('/pilih-perumahaan', [PerumahaanSelectController::class, 'store'])
        ->name('perumahaanSession.store');
});

// Route Dashboard Dashboard
Route::middleware('auth')->prefix('/dashboard')->group(function () {
    Route::get('/marketing', [MarketingDashboardController::class, 'index'])
        ->name('dashboard.marketing.index');
});

// Etalase Group
Route::middleware('auth')->prefix('etalase')->group(function () {

    Route::resource('perumahaan', controller: PerumahaanController::class);
    // nested resource create, store, edit, update, destroy untuk Tahap
    Route::get('perumahaan/{perumahaan:slug}/tahap/create', [TahapController::class, 'create'])
        ->name('tahap.create');
    Route::post('perumahaan/{perumahaan:slug}/tahap', [TahapController::class, 'store'])
        ->name('tahap.store');
    Route::get(
        'perumahaan/{perumahaan:slug}/tahap/{tahap:slug}/edit',
        [TahapController::class, 'edit']
    )->withoutScopedBindings()->name('tahap.edit');
    Route::put(
        'perumahaan/{perumahaan:slug}/tahap/{tahap:slug}',
        [TahapController::class, 'update']
    )->withoutScopedBindings()->name('tahap.update');
    Route::delete(
        'perumahaan/{perumahaan:slug}/tahap/{tahap:slug}',
        [TahapController::class, 'destroy']
    )->withoutScopedBindings()->name('tahap.destroy');

    // tahap type
    Route::post('tahapType/{tahap}', [TahapTypeController::class, 'store'])->name('tahapType.store');
    Route::delete('tahapType/{id}', [TahapTypeController::class, 'destroy'])->name('tahapType.destroy');

    // tahap kualifikasi blok
    Route::post('tahapKualifikasi/{tahap}', [TahapKualifikasiController::class, 'store'])->name('tahapKualifikasi.store');
    Route::put('tahapKualifikasi/{id}', [TahapKualifikasiController::class, 'update'])->name('tahapKualifikasi.update');
    Route::delete('tahapKualifikasi/{id}', [TahapKualifikasiController::class, 'destroy'])->name('tahapKualifikasi.destroy');

    Route::resource('tipe-unit', TypeController::class)->names('tipe-unit');
    Route::get('/tipe-unit/search', [TypeController::class, 'search'])->name('tipe-unit.search');
    // ajukan perubahaan harga tipe unit
    Route::put('/tipe-unit/{slug}/ajukan-harga', [TypeController::class, 'ajukanPerubahanHarga'])->name('tipe-unit.ajukanHarga');

    Route::resource('kualifikasi-blok', KualifikasiBlokController::class)->names('kualifikasi-blok');

    Route::resource('/blok', BlokController::class)->names('blok');

    Route::get('/unit', [UnitController::class, 'indexGlobal'])
        ->name('unit.indexGlobal');

    Route::prefix('{perumahaan:slug}')->group(function () {
        Route::resource('unit', UnitController::class)
            ->names('unit'); // jangan pakai except('index')
    });

    Route::get(
        '/perumahaan/{perumahaan:slug}/tahap-json',
        [EtalaseJsonController::class, 'listByPerumahaan']
    )
        ->name('tahap.list'); // untuk ambil tahap sesuai perumahaan (ajax)
    // Ambil Unit berdasar  kan tahap
    Route::get('/tahap/{tahapId}/unit-json', [EtalaseJsonController::class, 'getUnitsByTahap']);
    Route::get('/etalase/unit/{id}/harga-json', [EtalaseJsonController::class, 'getUnitHarga']);

    // Perubahaan harga untuk manager dukungan dan layanan
    Route::prefix('perubahan-harga')->group(function () {
        Route::get('/tipe-unit', [PerubahaanHargaTypeUnitController::class, 'index'])
            ->name('perubahan-harga.tipe-unit.index');
        Route::delete('/tipe-unit/{id}/tolak', [PerubahaanHargaTypeUnitController::class, 'tolakPengajuan'])
            ->name('perubahan-harga.tipe-unit.tolakPengajuan');
        Route::post('/tipe-unit/{id}/approve', [PerubahaanHargaTypeUnitController::class, 'approvePengajuan'])
            ->name('perubahan-harga.tipe-unit.approvePengajuan');

        // Route::get('/tahap-kualifikasi-blok', [KualifikasiBlokController::class, 'perubahanHargaTahapKualifikasiBlok'])
        //     ->name('harga-tahap-kualifikasi-blok.index');
    });
});

// Marketing Group
Route::middleware('auth')->prefix('marketing')->group(function () {

    Route::get('/akun-user/expired', [AkunUserController::class, 'expired'])->name('marketing.akunUser.expired');
    Route::resource('/akun-user', AkunUserController::class)->names('marketing.akunUser');

    Route::resource('/pemesanan-unit', PemesananUnitController::class)->names('marketing.pemesananUnit');

    // Route::resource('/manage-pemesanan', ManagePemesananController::class)->names('marketing.managePemesanan');
    Route::prefix('manage-pemesanan')->group(function () {
        // export ppjb word
        Route::get('/export/ppjbKPR/{id}', [ManagePemesananController::class, 'exportWordKPR'])
            ->name('ppjbKPR.export.word');
        Route::get('/export/ppjbCASH/{id}', [ManagePemesananController::class, 'exportWordCASH'])
            ->name('ppjbCASH.export.word');
        Route::get('/export-excel', [ManagePemesananController::class, 'exportExcel'])
            ->name('marketing.managePemesanan.exportExcel');

        // 🔹 Rincian Tagihan
        Route::get('/rincian-tagihan/{id}', [ManagePemesananController::class, 'rincianTagihan'])
            ->name('marketing.rincianTagihan');

        Route::get('/', [ManagePemesananController::class, 'index'])
            ->name('marketing.managePemesanan.index');
        Route::get('/{id}', [ManagePemesananController::class, 'show'])
            ->name('marketing.managePemesanan.show');

        // kpr pilih bank dulu jika belum ada
        Route::post('/kelengkapan-berkas-kpr/set-bank/{id}', [KelengkapanBerkasKprController::class, 'setBank'])->name('marketing.managePemesanan.kelengkapanBerkasKpr.setBank');
        Route::get('/kelengkapan-berkas-kpr/{id}', [KelengkapanBerkasKprController::class, 'editKpr'])
            ->name('marketing.kelengkapanBerkasKpr.editKpr');
        Route::put('/kelengkapan-berkas-kpr/{id}', [KelengkapanBerkasKprController::class, 'updateKpr'])
            ->name('marketing.kelengkapanBerkasKpr.updateKpr');

        Route::get('/kelengkapan-berkas-cash/{id}', [KelengkapanBerkasCashController::class, 'editCash'])->name('marketing.kelengkapanBerkasCash.editCash');
        Route::put('/kelengkapan-berkas-cash/{id}', [KelengkapanBerkasCashController::class, 'updateCash'])->name('marketing.kelengkapanBerkasCash.updateCash');

        // pengajuan pembatalan pemesanan unit
        Route::post('/pengajuan-pembatalan/store', [PengajuanPembatalanController::class, 'store'])
            ->name('marketing.pengajuanPembatalan.store');

        // pindah unit route
        Route::get('/pemesanan/pindah-unit/{id}', [PindahUnitController::class, 'createPengajuan'])
            ->name('marketing.pindahUnit.createPengajuan');

        // Route::post('/pemesanan/pindah-unit', [PindahUnitController::class, 'store'])
        //     ->name('marketing.pemesanan.pindahUnit.store');
    });

    // Kelola Pemesanan Agent
    Route::get('/manage-pemesanan-agent/export-excel', [ManagePemesananAgentController::class, 'exportExcel'])
        ->name('marketing.managePemesananAgent.exportExcel');
    Route::get('/manage-pemesanan-agent', [ManagePemesananAgentController::class, 'index'])
        ->name('marketing.managePemesananAgent.index');
    Route::get('/manage-pemesanan-agent/{id}', [ManagePemesananAgentController::class, 'show'])
        ->name('marketing.managePemesananAgent.show');

    // Ganti Unit (Private)
    Route::get('/ganti-unit', [GantiUnitController::class, 'index'])->name('marketing.gantiUnit.index');
    Route::post('/ganti-unit', [GantiUnitController::class, 'store'])->name('marketing.gantiUnit.store');

    // pengajuan pemesanan unit
    Route::resource('/pengajuan-pemesanan', PengajuanPemesananController::class)->names('marketing.pengajuanPemesanan');

    // 🟡 Route tambahan untuk aksi tolak & approve
    Route::patch('/pengajuan-pemesanan/{id}/approve', [PengajuanPemesananController::class, 'approve'])->name('marketing.pengajuanPemesanan.approve');
    Route::patch('/pengajuan-pemesanan/{id}/reject', [PengajuanPemesananController::class, 'reject'])->name('marketing.pengajuanPemesanan.reject');

    // routoe pengajuan pembatalan pemesanan unit
    Route::get('/pengajuan-pembatalan', [PengajuanPembatalanController::class, 'ListPengajuan'])
        ->name('marketing.pengajuan-pembatalan.listPengajuan');
    Route::get('/pengajuan-pembatalan/{id}', [PengajuanPembatalanController::class, 'show'])
        ->name('marketing.pengajuan-pembatalan.show');
    // 🔹 Route Keputusan Proyek Manager
    Route::patch('/pengajuan-pembatalan/{id}/keputusan-pemasaran', [PengajuanPembatalanController::class, 'keputusanProjectManager'])
        ->name('marketing.pengajuan-pembatalan.keputusan-pemasaran');
    // 🔹 Route Keputusan Manager Keuangan (nanti kita isi belakangan)
    Route::patch('/pengajuan-pembatalan/{id}/keputusan-keuangan', [PengajuanPembatalanController::class, 'keputusanKeuangan'])
        ->name('marketing.pengajuan-pembatalan.keputusan-keuangan');

    Route::prefix('adendum')->group(function () {
        // Buat Adendum
        Route::get('/', [AdendumController::class, 'index'])
            ->name('marketing.adendum.index');
        // Adendum Cara Bayar
        Route::get('/cara-bayar', [AdendumController::class, 'caraBayar'])
            ->name('marketing.adendum.caraBayar');
        // Store Adendum
        Route::post('/store', [AdendumController::class, 'store'])
            ->name('marketing.adendum.store');

        // LIST Adendum
        Route::get('/list', [AdendumListController::class, 'index'])
            ->name('marketing.adendum.list');
        Route::get('/list/{id}', [AdendumListController::class, 'show'])
            ->name('marketing.adendum.detail');
        Route::patch('/list/{id}/approve', [AdendumListController::class, 'approve'])
            ->name(name: 'marketing.adendum.approve');
        Route::patch('/list/{id}/reject', [AdendumListController::class, 'reject'])
            ->name('marketing.adendum.reject');
    });

    // route setting ppjb
    Route::prefix('/setting')->group(function () {
        // halaman utama setting
        Route::get('/', [SettingPpjbController::class, 'listSettingPPJB'])
            ->name('settingPPJB.index');

        /**
         * =========================
         * PROMO (Cash & KPR)
         * =========================
         */
        Route::prefix('/promo')->group(function () {
            // Cash
            Route::get('/cash/edit', [SettingPromoPpjbController::class, 'editCash'])
                ->name('settingPPJB.promoCash.edit');
            Route::post('/cash', [SettingPromoPpjbController::class, 'updateCash'])
                ->name('settingPPJB.promoCash.pengajuanUpdate');

            // KPR
            Route::get('/kpr/edit', [SettingPromoPpjbController::class, 'editKpr'])
                ->name('settingPPJB.promoKpr.edit');
            Route::post('/kpr', [SettingPromoPpjbController::class, 'updateKpr'])
                ->name('settingPPJB.promoKpr.pengajuanUpdate');

            // Riwayat Promo
            Route::get('/{type}/history', [SettingPromoPpjbController::class, 'history'])
                ->whereIn('type', ['cash', 'kpr'])
                ->name('settingPPJB.promo.history');

            // Approval & Penolakan
            Route::patch('/{promoBatch}/approve', [SettingPromoPpjbController::class, 'approvePengajuan'])
                ->name('settingPPJB.promo.approve');
            Route::delete('/{promoBatch}/reject', [SettingPromoPpjbController::class, 'rejectPengajuan'])
                ->name('settingPPJB.promo.reject');

            // Pembatalan & Nonaktif
            Route::delete('/{batch}', [SettingPromoPpjbController::class, 'cancelPengajuanPromo'])
                ->name('settingPPJB.promo.pengajuanCancel');
            Route::patch('/{batch}/nonAktif', [SettingPromoPpjbController::class, 'nonAktifPromo'])
                ->name('settingPPJB.promo.nonAktif');
        });

        /**
         * =========================
         * MUTU PPJB
         * =========================
         */
        Route::prefix('/mutu')->group(function () {
            Route::get('/edit', [SettingMutuPpjbController::class, 'edit'])->name('settingPPJB.mutu.edit');
            Route::post('/pengajuan-update', [SettingMutuPpjbController::class, 'pengajuanUpdate'])
                ->name('settingPPJB.mutu.pengajuanUpdate');
            Route::patch('/{batch}/nonaktif', [SettingMutuPpjbController::class, 'nonAktifMutu'])
                ->name('settingPPJB.mutu.nonAktif');
            Route::delete('/{batch}/cancel', [SettingMutuPpjbController::class, 'cancelPengajuanMutu'])
                ->name('settingPPJB.mutu.cancel');
            Route::get('/history', [SettingMutuPpjbController::class, 'history'])
                ->name('settingPPJB.mutu.history');
        });

        /**
         * =========================
         * BONUS CASH
         * =========================
         */
        Route::prefix('/bonus-cash')->group(function () {
            Route::get('/edit', [SettingBonusCashController::class, 'edit'])
                ->name('settingPPJB.bonusCash.edit');
            Route::post('/pengajuan-update', [SettingBonusCashController::class, 'pengajuanUpdate'])
                ->name('settingPPJB.bonusCash.pengajuanUpdate');
            Route::patch('/{batch}/nonaktif', [SettingBonusCashController::class, 'nonAktif'])
                ->name('settingPPJB.bonusCash.nonAktif');
            Route::delete('/{batch}/cancel', [SettingBonusCashController::class, 'cancelPengajuan'])
                ->name('settingPPJB.bonusCash.cancel');
            Route::get('/history', [SettingBonusCashController::class, 'history'])
                ->name('settingPPJB.bonusCash.history');

            // Approval & Penolakan
            Route::patch('/{bonusCash}/approve', [SettingBonusCashController::class, 'approvePengajuan'])
                ->name('settingPPJB.bonusCash.approve');
            Route::delete('/{bonusCash}/reject', [SettingBonusCashController::class, 'rejectPengajuan'])
                ->name('settingPPJB.bonusCash.reject');
        });


        /**
         * =========================
         * BONUS CASH
         * =========================
         */
        Route::prefix('/bonus-kpr')->group(function () {
            Route::get('/edit', [SettingBonusKprController::class, 'edit'])
                ->name('settingPPJB.bonusKpr.edit');
            Route::post('/pengajuan-update', [SettingBonusKprController::class, 'pengajuanUpdate'])
                ->name('settingPPJB.bonusKpr.pengajuanUpdate');
            Route::patch('/{batch}/nonaktif', [SettingBonusKprController::class, 'nonAktif'])
                ->name('settingPPJB.bonusKpr.nonAktif');
            Route::delete('/{batch}/cancel', [SettingBonusKprController::class, 'cancelPengajuan'])
                ->name('settingPPJB.bonusKpr.cancel');
            Route::get('/history', [SettingBonusKprController::class, 'history'])
                ->name('settingPPJB.bonusKpr.history');

            // // Approval & Penolakan
            Route::patch('/{bonusKpr}/approve', [SettingBonusKprController::class, 'approvePengajuan'])
                ->name('settingPPJB.bonusKpr.approve');
            Route::delete('/{bonusKpr}/reject', [SettingBonusKprController::class, 'rejectPengajuan'])
                ->name('settingPPJB.bonusKpr.reject');
        });

        /**
         * =========================
         * CARA BAYAR
         * =========================
         */
        Route::prefix('/cara-bayar')->group(function () {
            Route::get('/edit', [SettingCaraBayarController::class, 'editCaraBayar'])
                ->name('settingPPJB.caraBayar.edit');
            Route::post('/', [SettingCaraBayarController::class, 'updatePengajuan'])
                ->name('settingPPJB.caraBayar.updatePengajuan');
            Route::delete('/{caraBayar}', [SettingCaraBayarController::class, 'cancelPengajuanCaraBayar'])
                ->name('settingPPJB.caraBayar.cancelPengajuan');
            Route::patch('/{caraBayar}/nonaktif', [SettingCaraBayarController::class, 'nonAktifCaraBayar'])
                ->name('settingPPJB.caraBayar.nonAktif');
            Route::patch('/{caraBayar}/approve', [SettingCaraBayarController::class, 'approvePengajuanCaraBayar'])
                ->name('settingPPJB.caraBayar.approve');
            Route::delete('/{caraBayar}/reject', [SettingCaraBayarController::class, 'rejectPengajuanCaraBayar'])
                ->name('settingPPJB.caraBayar.reject');
        });

        /**
         * =========================
         * KETERLAMBATAN PEMBAYARAN
         * =========================
         */
        Route::prefix('/keterlambatan')->group(function () {
            Route::get('/edit', [SettingKeterlambatanController::class, 'editKeterlambatan'])
                ->name('settingPPJB.keterlambatan.edit');
            Route::post('/', [SettingKeterlambatanController::class, 'updatePengajuan'])
                ->name('settingPPJB.keterlambatan.updatePengajuan');
            Route::delete('/{keterlambatan}', [SettingKeterlambatanController::class, 'cancelPengajuanKeterlambatan'])
                ->name('settingPPJB.keterlambatan.cancelPengajuan');
            Route::patch('/{keterlambatan}/nonaktif', [SettingKeterlambatanController::class, 'nonAktifKeterlambatan'])
                ->name('settingPPJB.keterlambatan.nonAktif');
            Route::patch('/{keterlambatan}/approve', [SettingKeterlambatanController::class, 'approvePengajuan'])
                ->name('settingPPJB.keterlambatan.approve');
            Route::delete('/{keterlambatan}/reject', [SettingKeterlambatanController::class, 'rejectPengajuan'])
                ->name('settingPPJB.keterlambatan.reject');
        });

        /**
         * =========================
         * PEMBATALAN
         * =========================
         */
        Route::prefix('/pembatalan')->group(function () {
            Route::get('/edit', [SettingPembatalanController::class, 'editPembatalan'])
                ->name('settingPPJB.pembatalan.edit');
            Route::post('/', [SettingPembatalanController::class, 'updatePengajuan'])
                ->name('settingPPJB.pembatalan.updatePengajuan');
            Route::delete('/{pembatalan}', [SettingPembatalanController::class, 'cancelPengajuanPembatalan'])
                ->name('settingPPJB.pembatalan.cancelPengajuanPromo');
            Route::patch('/{pembatalan}/nonaktif', [SettingPembatalanController::class, 'nonAktifPembatalan'])
                ->name('settingPPJB.pembatalan.nonAktif');
            Route::patch('/{pembatalan}/approve', [SettingPembatalanController::class, 'approvePengajuanPembatalan'])
                ->name('settingPPJB.pembatalan.approve');
            Route::delete('/{pembatalan}/reject', [SettingPembatalanController::class, 'rejectPengajuanPembatalan'])
                ->name('settingPPJB.pembatalan.reject');
        });
    });

    // Route untuk master agen
    Route::prefix('master-agen')->group(function () {
        // agen crud controller
        Route::resource('/agen', AgenController::class)->names('marketing.agen');

        // Fee Agen routes
        Route::get('/fee-agen', [FeeAgenController::class, 'index'])->name('marketing.feeAgen.index');
        Route::post('/fee-agen', [FeeAgenController::class, 'store'])->name('marketing.feeAgen.store');
        Route::patch('/fee-agen/{feeAgen}/approve', [FeeAgenController::class, 'approve'])->name('marketing.feeAgen.approve');
        Route::delete('/fee-agen/{feeAgen}/reject', [FeeAgenController::class, 'reject'])->name('marketing.feeAgen.reject');
        Route::delete('/fee-agen/{feeAgen}/cancel', [FeeAgenController::class, 'cancel'])->name('marketing.feeAgen.cancel');
        Route::patch('/fee-agen/{feeAgen}/non-aktif', [FeeAgenController::class, 'nonAktif'])->name('marketing.feeAgen.nonAktif');
    });

    // Route untuk target marketing
    Route::prefix('target-marketing')->group(function () {
        Route::get('/target-penjualan', [TargetPenjualanController::class, 'index'])->name('marketing.target-penjualan.index');
        Route::post('/target-penjualan', [TargetPenjualanController::class, 'store'])->name('marketing.target-penjualan.store');

        Route::get('/anggaran-promosi', [AnggaranPromosiController::class, 'index'])->name('marketing.anggaran-promosi.index');
        Route::post('/anggaran-promosi', [AnggaranPromosiController::class, 'store'])->name('marketing.anggaran-promosi.store');
    });


    Route::prefix('api')->group(function () {
        Route::get('/setting-cara-bayar/{perumahaanId}', [SettingPpjbJsonController::class, 'showByPerumahaan'])
            ->name('api.setting-caraBayar.show');
    });
});

// Gudang
Route::middleware('auth')->prefix('gudang')->group(function () {

    // Stock Barang
    Route::get('/stok-barang', [StockBarangController::class, 'stockIndex'])
        ->name('gudang.stockBarang.index');
    Route::post('/stok-barang/toggle-freeze', [StockBarangController::class, 'toggleFreezeState'])->name('gudang.stockBarang.toggleFreeze');
    Route::get('/stok-barang/export-pdf', [StockBarangController::class, 'exportPdf'])->name('gudang.stockBarang.exportPdf');
    Route::get('/stok-barang/export-excel', [StockBarangController::class, 'exportExcel'])->name('gudang.stockBarang.exportExcel');

    // Audit Log Stok
    Route::get('/audit-log', [AuditLogStockController::class, 'index'])
        ->name('gudang.auditLog.index');
    Route::get('/audit-log/detail/{refType}/{refId}', [AuditLogStockController::class, 'getDocDetail'])
        ->name('gudang.auditLog.docDetail');


    // Route bertransaksi stok / barang yang dibatasi oleh Freeze Mode
    Route::middleware('check.freeze')->group(function () {
        // Transfer Stock Barang
        Route::get('/transfer-stock-barang', [TransferStockBarangController::class, 'create'])->name('gudang.transferStockBarang.create');
        Route::post('/transfer-stock-barang/store', [TransferStockBarangController::class, 'store'])->name('gudang.transferStockBarang.store');
        // Tranfer penyesuain stok ubs
        Route::get('/transfer-stock-penyesuain', [TransferPenyesuainStockController::class, 'create'])->name('gudang.transferStockBarang.createPenyesuaian');
        Route::post('/transfer-stock-penyesuain/store', [TransferPenyesuainStockController::class, 'store'])->name('gudang.transferStockBarang.storePenyesuaian');

        // Master Barang mutation
        Route::post('/master-barang', [MasterBarangController::class, 'store'])->name('gudang.masterBarang.store');
        Route::put('/master-barang/{id}', [MasterBarangController::class, 'update'])->name('gudang.masterBarang.update');
        Route::delete('/master-barang/{id}', [MasterBarangController::class, 'destroy'])->name('gudang.masterBarang.destroy');

        // Barang Rakitan
        Route::post('/produksi-rakitan', [ProduksiRakitanController::class, 'store'])->name('gudang.produksiRakitan.store');
        Route::delete('/produksi-rakitan/{id}', [ProduksiRakitanController::class, 'destroy'])->name('gudang.produksiRakitan.destroy');

        // Nota Masuk
        Route::get('/nota-barang-masuk/create', [NotaBarangMasukController::class, 'create'])->name('gudang.notaBarangMasuk.create');
        Route::post('/nota-barang-masuk/store', [NotaBarangMasukController::class, 'store'])->name('gudang.notaBarangMasuk.store');
        Route::patch('/draft-nota-masuk/{nomorNota}', [DraftNotaMasukController::class, 'update'])->name('gudang.draftNotaMasuk.update');
        Route::patch('/draft-nota-masuk/{nomorNota}/post', [DraftNotaMasukController::class, 'post'])->name('gudang.draftNotaMasuk.submit');
        Route::delete('/draft-nota-masuk/{nomorNota}', [DraftNotaMasukController::class, 'destroy'])->name('gudang.draftNotaMasuk.destroy');
        Route::delete('/nota-barang-masuk/{nomorNota}', [DaftarNotaMasukController::class, 'destroy'])->name('gudang.daftarNotaMasuk.destroy');

        // Barang Rusak
        Route::get('/barang-rusak/create', [BarangRusakController::class, 'create'])->name('gudang.barangRusak.create');
        Route::post('/barang-rusak', [BarangRusakController::class, 'store'])->name('gudang.barangRusak.store');
        Route::patch('/barang-rusak/{nomorBarangRusak}/cancel', [BarangRusakController::class, 'cancel'])->name('gudang.barangRusak.cancel');

        // ACC & Tolak Permintaan / Return Gudang
        Route::patch('/permintaan-barang/pembangunan-unit/{id}/acc', [PermintaanBarangPembangunanUnitController::class, 'accBarangOrder'])->name('gudang.permintaanBarang.pembangunanUnit.acc');
        Route::patch('/permintaan-barang/pembangunan-unit/{id}/tolak', [PermintaanBarangPembangunanUnitController::class, 'tolakBarangOrder'])->name('gudang.permintaanBarang.pembangunanUnit.tolak');
        Route::patch('/permintaan-barang/pembangunan-unit/{id}/resubmit', [PermintaanBarangPembangunanUnitController::class, 'resubmitBarangOrder'])->name('gudang.permintaanBarang.pembangunanUnit.resubmit');
        Route::patch('/permintaan-barang/pembangunan-unit/return/{id}/acc', [PermintaanBarangPembangunanUnitController::class, 'accBarangReturn'])->name('gudang.permintaanBarang.pembangunanUnit.accReturn');
        Route::patch('/permintaan-barang/pembangunan-unit/return/{id}/reject', [PermintaanBarangPembangunanUnitController::class, 'rejectBarangReturn'])->name('gudang.permintaanBarang.pembangunanUnit.rejectReturn');
        Route::patch('/permintaan-barang/{id}/acc', [PermintaanBarangController::class, 'acc'])->name('gudang.permintaanBarang.acc');
        Route::patch('/permintaan-barang/{id}/tolak', [PermintaanBarangController::class, 'tolak'])->name('gudang.permintaanBarang.tolak');
        Route::patch('/permintaan-barang/{id}/resubmit', [PermintaanBarangController::class, 'resubmit'])->name('gudang.permintaanBarang.resubmit');
        Route::put('/permintaan-barang/{id}', [PermintaanBarangPembangunanUnitController::class, 'update'])->name('gudang.permintaanBarang.update');
        Route::delete('/permintaan-barang/{id}', [PermintaanBarangController::class, 'destroy'])->name('gudang.permintaanBarang.destroy');
        Route::patch('/permintaan-barang/pembangunan-kawasan/return/{id}/acc', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanKawasanController::class, 'accBarangReturn'])->name('gudang.permintaanBarang.pembangunanKawasan.accReturn');
        Route::patch('/permintaan-barang/pembangunan-kawasan/return/{id}/reject', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanKawasanController::class, 'rejectBarangReturn'])->name('gudang.permintaanBarang.pembangunanKawasan.rejectReturn');
        Route::patch('/permintaan-barang/pembangunan-proyek/return/{id}/acc', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanProyekController::class, 'accBarangReturn'])->name('gudang.permintaanBarang.pembangunanProyek.accReturn');
        Route::patch('/permintaan-barang/pembangunan-proyek/return/{id}/reject', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanProyekController::class, 'rejectBarangReturn'])->name('gudang.permintaanBarang.pembangunanProyek.rejectReturn');
    });

    // Transfer Stock Read-only & Helpers
    Route::get('/transfer-stock-barang/satuan-dan-stok/{barangId}', [TransferStockBarangController::class, 'getSatuanDanStok']);
    Route::get('/riwayat-transfer-stock', [TransferStockBarangController::class, 'riwayatTransferStock'])->name('gudang.transferStockBarang.riwayatTransferStock');
    Route::get('/riwayat-transfer-stock/{nomorTransfer}', [TransferStockBarangController::class, 'showRiwayatTransferStock'])->name('gudang.transferStockBarang.riwayatTransferStock.show');

    // Transfer Stock Barang (UBS ke UBS dengan Approval SPV)
    Route::get('/transfer-stock-barang', [TransferStockBarangController::class, 'create'])->name('gudang.transferStockBarang.create');
    Route::post('/transfer-stock-barang/store', [TransferStockBarangController::class, 'store'])->name('gudang.transferStockBarang.store');
    Route::get('/transfer-stock-barang/satuan-dan-stok/{barangId}/{ubsId}', [TransferStockBarangController::class, 'getSatuanDanStok']);
    Route::get('/transfer-stock-barang/{nomorTransfer}/edit', [TransferStockBarangController::class, 'edit'])->name('gudang.transferStockBarang.edit');
    Route::post('/transfer-stock-barang/{nomorTransfer}/update', [TransferStockBarangController::class, 'update'])->name('gudang.transferStockBarang.update');


    // Daftar Transfer Stock Pengajuan dll
    Route::get('/daftar-transfer-stock', [TransferStockBarangController::class, 'index'])->name('gudang.transferStockBarang.daftar.index');
    Route::get('/daftar-transfer-stock/{nomorTransfer}', [TransferStockBarangController::class, 'daftarShow'])->name('gudang.transferStockBarang.daftar.show');
    Route::get('/daftar-transfer-stock/{nomorTransfer}/pdf', [TransferStockBarangController::class, 'printPdf'])->name('gudang.transferStockBarang.daftar.pdf');
    Route::patch('/daftar-transfer-stock/{nomorTransfer}/approve', [TransferStockBarangController::class, 'approvePengajuan'])->name('gudang.transferStockBarang.daftar.approve');
    Route::patch('/daftar-transfer-stock/{nomorTransfer}/reject', [TransferStockBarangController::class, 'rejectPengajuan'])->name('gudang.transferStockBarang.daftar.reject');
    Route::delete('/daftar-transfer-stock/{nomorTransfer}/destroy', [TransferStockBarangController::class, 'destroy'])->name('gudang.transferStockBarang.daftar.destroy');

    // Tranfer penyesuain stok ubs
    Route::get('/transfer-stock-penyesuain', [TransferPenyesuainStockController::class, 'create'])->name('gudang.transferStockBarang.createPenyesuaian');
    Route::post('/transfer-stock-penyesuain/store', [TransferPenyesuainStockController::class, 'store'])->name('gudang.transferStockBarang.storePenyesuaian');
    Route::get('/transfer-stock-penyesuain/stok/{barangId}/{ubsId}', [TransferPenyesuainStockController::class, 'getStokBarangUbsHub']);

    // Master Supplier
    Route::resource('/master-supplier', MasterSupplierController::class)->names('gudang.masterSupplier');

    // Master satuan barang controller
    Route::resource('/master-satuan-barang', MasterSatuanBarangController::class)->names('gudang.masterSatuanBarang');

    // Master Barang Read Only Resource (Store/Update/Destroy moved inside check.freeze)
    Route::get('/master-barang', [MasterBarangController::class, 'index'])->name('gudang.masterBarang.index');
    Route::get('/master-barang/create', [MasterBarangController::class, 'create'])->name('gudang.masterBarang.create');
    Route::get('/master-barang/{id}', [MasterBarangController::class, 'show'])->name('gudang.masterBarang.show');
    Route::get('/master-barang/{id}/edit', [MasterBarangController::class, 'edit'])->name('gudang.masterBarang.edit');

    // Barang Rakitan > Komposisi Rakitan
    Route::resource('/barang-rakitan', KomposisiRakitanController::class)->names('gudang.komposisiRakitan');

    // Barang Rakitan > Produksi Rakitan
    Route::get('/produksi-rakitan', [ProduksiRakitanController::class, 'index'])->name('gudang.produksiRakitan.index');
    Route::get('/produksi-rakitan/create', [ProduksiRakitanController::class, 'create'])->name('gudang.produksiRakitan.create');
    Route::get('/produksi-rakitan/{id}', [ProduksiRakitanController::class, 'show'])->name('gudang.produksiRakitan.show');

    // Tambah Nota Masuk
    // Tambah Nota Masuk Helpers
    Route::get('/barang/{id}/satuan', [NotaBarangMasukController::class, 'getSatuan']);
    // List Draft nota masuk
    Route::get('/draft-nota-masuk', [DraftNotaMasukController::class, 'index'])->name('gudang.draftNotaMasuk.index');
    Route::get('/draft-nota-masuk/{id}', [DraftNotaMasukController::class, 'edit'])->name('gudang.draftNotaMasuk.edit');
    Route::patch('/draft-nota-masuk/{id}', [DraftNotaMasukController::class, 'update'])->name('gudang.draftNotaMasuk.update'); /// update change draft nota masuk
    Route::patch('/draft-nota-masuk/{id}/post', [DraftNotaMasukController::class, 'post'])->name('gudang.draftNotaMasuk.submit'); /// submit draft nota masuk menjadi posting
    Route::delete('/draft-nota-masuk/{id}', [DraftNotaMasukController::class, 'destroy'])->name('gudang.draftNotaMasuk.destroy');

    // Daftar & Rekap Nota Masuk
    Route::get('/nota-barang-masuk', [DaftarNotaMasukController::class, 'index'])->name('gudang.daftarNotaMasuk.index');
    Route::get('/rekap-nota-masuk', [RekapNotaMasukController::class, 'index'])->name('gudang.rekapNotaMasuk.index');
    Route::get('/nota-barang-masuk/{nomorNota}', [DaftarNotaMasukController::class, 'show'])->name('gudang.daftarNotaMasuk.show');

    // Barang Rusak
    Route::get('/barang-rusak', [BarangRusakController::class, 'index'])->name('gudang.barangRusak.index');
    Route::get('/barang-rusak/satuan-dan-stok/{barangId}', [BarangRusakController::class, 'getSatuanDanStok'])->name('gudang.barangRusak.satuanStok');
    Route::get('/barang-rusak/{nomorBarangRusak}', [BarangRusakController::class, 'show'])->name('gudang.barangRusak.show');

    // Permintaan Barang Proyek
    Route::get('/permintaan-barang', [PermintaanBarangController::class, 'index'])->name('gudang.permintaanBarang.index');
    Route::get('/permintaan-barang/riwayat', [PermintaanBarangController::class, 'history'])->name('gudang.permintaanBarang.history');
    Route::get('/permintaan-barang/pembangunan-unit/create', [PermintaanBarangPembangunanUnitController::class, 'create'])->name('gudang.permintaanBarang.pembangunanUnit.create');
    Route::get('/permintaan-barang/pembangunan-unit/qc-list/{pembangunanUnitId}', [PermintaanBarangPembangunanUnitController::class, 'getQcList'])->name('gudang.permintaanBarang.pembangunanUnit.qcList');
    Route::get('/permintaan-barang/{id}/edit', [PermintaanBarangPembangunanUnitController::class, 'edit'])->name('gudang.permintaanBarang.edit');
    Route::get('/permintaan-barang/{id}', [PermintaanBarangController::class, 'show'])->name('gudang.permintaanBarang.show');

    // Retur Barang (Gudang CRUD) - read only
    Route::get('/return-barang/create', [PermintaanBarangPembangunanUnitController::class, 'createReturn'])->name('gudang.returnBarang.create');
    Route::get('/return-barang/{id}/edit', [PermintaanBarangPembangunanUnitController::class, 'editReturn'])->name('gudang.returnBarang.edit');
    Route::get('/return-barang/qc-summary/{qcId}', [PermintaanBarangPembangunanUnitController::class, 'returnSummaryQc'])->name('gudang.returnBarang.qcSummary');
    Route::get('/return-barang/kawasan-summary/{kawasanId}', [PermintaanBarangPembangunanUnitController::class, 'returnSummaryKawasan'])->name('gudang.returnBarang.kawasanSummary');
    Route::get('/return-barang/proyek-summary/{proyekId}', [PermintaanBarangPembangunanUnitController::class, 'returnSummaryProyek'])->name('gudang.returnBarang.proyekSummary');

    // Retur Barang (Gudang CRUD) - mutasi, dibatasi Freeze Mode
    Route::middleware('check.freeze')->group(function () {
        Route::post('/return-barang/store', [PermintaanBarangPembangunanUnitController::class, 'storeReturn'])->name('gudang.returnBarang.store');
        Route::put('/return-barang/{id}', [PermintaanBarangPembangunanUnitController::class, 'updateReturn'])->name('gudang.returnBarang.update');
        Route::patch('/return-barang/{id}/resubmit', [PermintaanBarangPembangunanUnitController::class, 'resubmitReturn'])->name('gudang.returnBarang.resubmit');
    });

    // Retur Barang Unit (Gudang)
    Route::get('/return-barang/unit', [PermintaanBarangPembangunanUnitController::class, 'indexReturn'])->name('gudang.returnBarang.unit.index');
    Route::get('/return-barang/unit/riwayat', [PermintaanBarangPembangunanUnitController::class, 'historyReturn'])->name('gudang.returnBarang.unit.history');
    Route::get('/return-barang/unit/{id}', [PermintaanBarangPembangunanUnitController::class, 'showReturn'])->name('gudang.returnBarang.unit.show');

    // Retur Barang Kawasan (Gudang)
    Route::get('/return-barang/kawasan', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanKawasanController::class, 'indexReturn'])->name('gudang.returnBarang.kawasan.index');
    Route::get('/return-barang/kawasan/riwayat', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanKawasanController::class, 'historyReturn'])->name('gudang.returnBarang.kawasan.history');
    Route::get('/return-barang/kawasan/{id}', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanKawasanController::class, 'showReturn'])->name('gudang.returnBarang.kawasan.show');

    // Retur Barang Proyek Mangoon (Gudang)
    Route::get('/return-barang/proyek', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanProyekController::class, 'indexReturn'])->name('gudang.returnBarang.proyek.index');
    Route::get('/return-barang/proyek/riwayat', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanProyekController::class, 'historyReturn'])->name('gudang.returnBarang.proyek.history');
    Route::get('/return-barang/proyek/{id}', [\App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanProyekController::class, 'showReturn'])->name('gudang.returnBarang.proyek.show');


    // Master Tukang Harian
    Route::get('/master-tukang-harian', [MasterTukangController::class, 'index'])->name('gudang.masterTukang.index');
    Route::get('/master-tukang-harian/create', [MasterTukangController::class, 'create'])->name('gudang.masterTukang.create');
    Route::post('/master-tukang-harian', [MasterTukangController::class, 'store'])->name('gudang.masterTukang.store');
    Route::get('/master-tukang-harian/{id}/edit', [MasterTukangController::class, 'edit'])->name('gudang.masterTukang.edit');
    Route::put('/master-tukang-harian/{id}', [MasterTukangController::class, 'update'])->name('gudang.masterTukang.update');
    Route::delete('/master-tukang-harian/{id}', [MasterTukangController::class, 'destroy'])->name('gudang.masterTukang.destroy');

    // Pengajuan Upah Harian Tukang dari sisi gudang
    Route::get('/pengajuan-upahharian-tukang', [PengajuanUpahHarianTukangController::class, 'index'])->name('gudang.pengajuanUpahHarianTukang.index');
    Route::get('/pengajuan-upahharian-tukang-mangoon', [PengajuanUpahHarianTukangController::class, 'indexMangoon'])->name('gudang.pengajuanUpahHarianTukang.indexMangoon');
    Route::get('/pengajuan-upahharian-tukang/create', [PengajuanUpahHarianTukangController::class, 'create'])->name('gudang.pengajuanUpahHarianTukang.create');
    Route::get('/pengajuan-upahharian-tukang-mangoon/create', [PengajuanUpahHarianTukangController::class, 'createMangoon'])->name('gudang.pengajuanUpahHarianTukang.createMangoon');
    Route::post('/pengajuan-upahharian-tukang/store-draft', [PengajuanUpahHarianTukangController::class, 'storeDraft'])->name('gudang.pengajuanUpahHarianTukang.storeDraft');
    Route::post('/pengajuan-upahharian-tukang-mangoon/store-draft', [PengajuanUpahHarianTukangController::class, 'storeDraftMangoon'])->name('gudang.pengajuanUpahHarianTukang.storeDraftMangoon');
    Route::get('/pengajuan-upahharian-tukang/{id}/edit', [PengajuanUpahHarianTukangController::class, 'edit'])->name('gudang.pengajuanUpahHarianTukang.edit');
    Route::get('/pengajuan-upahharian-tukang-mangoon/{id}/edit', [PengajuanUpahHarianTukangController::class, 'editMangoon'])->name('gudang.pengajuanUpahHarianTukang.editMangoon');
    Route::put('/pengajuan-upahharian-tukang/{id}/draft', [PengajuanUpahHarianTukangController::class, 'updateDraft'])->name('gudang.pengajuanUpahHarianTukang.updateDraft');
    Route::put('/pengajuan-upahharian-tukang-mangoon/{id}/draft', [PengajuanUpahHarianTukangController::class, 'updateDraftMangoon'])->name('gudang.pengajuanUpahHarianTukang.updateDraftMangoon');
    Route::put('/pengajuan-upahharian-tukang/{id}/submit', [PengajuanUpahHarianTukangController::class, 'submit'])->name('gudang.pengajuanUpahHarianTukang.submitDraft');
    Route::put('/pengajuan-upahharian-tukang-mangoon/{id}/submit', [PengajuanUpahHarianTukangController::class, 'submitMangoon'])->name('gudang.pengajuanUpahHarianTukang.submitDraftMangoon');
    Route::delete('/pengajuan-upahharian-tukang/{id}', [PengajuanUpahHarianTukangController::class, 'destroy'])->name('gudang.pengajuanUpahHarianTukang.destroy');
    Route::delete('/pengajuan-upahharian-tukang-mangoon/{id}', [PengajuanUpahHarianTukangController::class, 'destroyMangoon'])->name('gudang.pengajuanUpahHarianTukang.destroyMangoon');
    Route::get('/pengajuan-upahharian-tukang/{id}/detail', [PengajuanUpahHarianTukangController::class, 'detail'])->name('gudang.pengajuanUpahHarianTukang.detail');
    Route::get('/pengajuan-upahharian-tukang-mangoon/{id}/detail', [PengajuanUpahHarianTukangController::class, 'detailMangoon'])->name('gudang.pengajuanUpahHarianTukang.detailMangoon');
    Route::post('/pengajuan-upahharian-tukang/{id}/cancel', [PengajuanUpahHarianTukangController::class, 'cancel'])->name('gudang.pengajuanUpahHarianTukang.cancel');
    Route::post('/pengajuan-upahharian-tukang-mangoon/{id}/cancel', [PengajuanUpahHarianTukangController::class, 'cancelMangoon'])->name('gudang.pengajuanUpahHarianTukang.cancelMangoon');
});

// keuangan Group
Route::middleware('auth')->prefix('keuangan')->group(function () {
    Route::get('/', function () {
        return view('superadmin.dashboard.index');
    })->name('superadmin.dashboard.index');

    // Periode Keuangan
    Route::resource('periode-keuangan', PeriodeKeuanganController::class)->names('keuangan.periodeKeuangan');

    // Kategori Akun
    Route::get('/kategori-akun', [KategoriAkunKeuanganController::class, 'index'])->name('keuangan.kategoriAkun.index');

    // Akun Keuangan
    Route::resource('/akun-keuangan', controller: AkunKeuanganController::class)->names('keuangan.akunKeuangan');

    // Transaksi jurnal
    Route::get('/transaksi-jurnal/generate-nomor', [TransaksiJurnalController::class, 'generateNomor'])->name('keuangan.transaksiJurnal.generateNomor');
    Route::get('/transaksi-jurnal', [TransaksiJurnalController::class, 'create'])->name('keuangan.transaksiJurnal.create');
    Route::post('/transaksi-jurnal', [TransaksiJurnalController::class, 'store'])->name('keuangan.transaksiJurnal.store');
    Route::get('/transaksi-jurnal/{id}/edit', [TransaksiJurnalController::class, 'edit'])->name('keuangan.transaksiJurnal.edit');
    Route::put('/transaksi-jurnal/{id}', [TransaksiJurnalController::class, 'update'])->name('keuangan.transaksiJurnal.update');
    Route::delete('/transaksi-jurnal/{id}', [TransaksiJurnalController::class, 'destroy'])->name('keuangan.transaksiJurnal.destroy');


    Route::prefix('/laporan')->group(function () {
        Route::get('/jurmal-umum', [LaporanJurnalController::class, 'index'])->name('keuangan.laporanJurnal.index');
        Route::get('/jurnal-umum/export-excel', [LaporanJurnalController::class, 'exportExcel'])->name('keuangan.laporanJurnal.exportExcel');
        Route::get('/jurnal-umum/export-pdf', [LaporanJurnalController::class, 'exportPdf'])->name('keuangan.laporanJurnal.exportPdf');

        Route::get('/buku-besar', [BukuBesarController::class, 'index'])->name('keuangan.bukuBesar.index');
        Route::get('/buku-besar/export-excel', [BukuBesarController::class, 'exportExcel'])->name('keuangan.bukuBesar.exportExcel');
        Route::get('/buku-besar/export-pdf', [BukuBesarController::class, 'exportPdf'])->name('keuangan.bukuBesar.exportPdf');

        Route::get('/neraca-saldo', [NeracaSaldoController::class, 'index'])->name('keuangan.neracaSaldo.index');
        Route::get('/neraca-saldo/export-excel', [NeracaSaldoController::class, 'exportExcel'])->name('keuangan.neracaSaldo.exportExcel');
        Route::get('/neraca-saldo/export-pdf', [NeracaSaldoController::class, 'exportPdf'])->name('keuangan.neracaSaldo.exportPdf');
    });

    // Upah harian tukang - keuangan
    Route::prefix('/upahHarian')->group(function () {

        // daftar pengajuan upah harian tukang
        Route::get('/', [DaftarUpahHarianTukangKeuanganController::class, 'index'])->name('keuangan.daftarUpahHarian.index');
        Route::get('/{id}/detail', [DaftarUpahHarianTukangKeuanganController::class, 'detail'])->name('keuangan.daftarUpahHarian.detail');
        Route::post('/{id}/update-bon', [DaftarUpahHarianTukangKeuanganController::class, 'updateBon'])->name('keuangan.daftarUpahHarian.updateBon');
        // Route acc dari pengajuan upah harian tukang
        Route::patch('/{upahHarianTukang}/approve',[DaftarUpahHarianTukangKeuanganController::class, 'accPengajuan'])->name('keuangan.daftarUpahHarian.accPengajuan');
        // route untuk expor detail dari pengajuan upah harian export
        Route::get('/export-excel', [DaftarUpahHarianTukangKeuanganController::class, 'exportExcel'])->name('keuangan.daftarUpahHarian.exportExcel');

        // Daftar Riwayat upah harian tukang
        Route::get('/riwayat', [RiwayatUpahHarianTukangController::class, 'index'])->name('keuangan.riwayatUpahHarian.index');
        Route::get('/riwayat/{id}/detail', [RiwayatUpahHarianTukangController::class, 'detail'])->name('keuangan.riwayatUpahHarian.detail');
    });

});

// Produksi
Route::middleware('auth')->prefix('produksi')->group(function () {
    // Master QC RAP
    Route::resource('master-qc-rap', MasterQcRapController::class)->middleware('can:produksi.properti.master-qc-rap.read')->names('produksi.masterQcRap');

    // Penamaan Upah
    Route::resource('penamaan-upah', PenamaanUpahController::class)->middleware('can:produksi.manajemen-upah.penamaan-upah.read')->names('produksi.masterUpah');

    // Permintaan Dibangun
    Route::middleware('can:produksi.properti.permintaan-dibangun.read')->group(function () {
        Route::resource('permintaan-dibangun', PermintaanDibangunController::class)->except(['store'])->names('produksi.pengajuanPembangunanUnit');
        Route::get('/tahap/{tahapId}/unit-json', [PermintaanDibangunController::class, 'getUnitsByTahap']);
        Route::post('/konfirmasi-pembangunan', [KonfirmasiPembangunanController::class, 'konfirmasi'])->name('produksi.konfirmasiPembangunan');
    });
    Route::post('permintaan-dibangun', [PermintaanDibangunController::class, 'store'])
        ->middleware('can:etalase.unit.pengajuan-pembangunan')
        ->name('produksi.pengajuanPembangunanUnit.store');

    // Pembangunan Unit
    Route::middleware('can:produksi.properti.pembangunan-unit.read')->group(function () {
        Route::resource('pembangunan-unit', PembangunanUnitController::class)->names('produksi.pembangunanUnit');
        Route::post('pembangunan-unit/{id}/update-serah-terima', [PembangunanUnitController::class, 'updateSerahTerima'])
            ->name('produksi.pembangunanUnit.updateSerahTerima');
        Route::post('pembangunan-unit/task/{id}/update', [PembangunanUnitController::class, 'updateTask'])
            ->name('produksi.pembangunanUnit.updateTask');
        Route::post('pembangunan-unit/update-task-note/{id}', [PembangunanUnitController::class, 'updateTaskNote'])
            ->name('produksi.pembangunanUnit.updateTaskNote');

        Route::middleware('check.freeze')->group(function () {
            Route::post('pembangunan-unit/order-barang', [PembangunanUnitOrderBarangController::class, 'store'])
                ->name('produksi.pembangunanUnit.orderStore');
            Route::delete('pembangunan-unit/order-barang/{id}', [PembangunanUnitOrderBarangController::class, 'destroy'])
                ->name('produksi.pembangunanUnit.orderDestroy');
            Route::post('pembangunan-unit/return-barang', [PembangunanUnitBarangReturnController::class, 'store'])
                ->name('produksi.pembangunanUnit.returnStore');
        });
        // Return barang per-QC (baru)
        Route::get('pembangunan-unit/return-barang/{qcId}/summary', [PembangunanUnitBarangReturnController::class, 'summary'])
            ->name('produksi.pembangunanUnit.returnSummary');

        Route::post('pembangunan-unit/upah-pengajuan', [PembangunanUnitPengajuanUpahController::class, 'store'])
            ->name('produksi.pembangunanUnit.upahStore');
        Route::delete('pembangunan-unit/upah-pengajuan/{id}', [PembangunanUnitPengajuanUpahController::class, 'destroy'])
            ->name('produksi.pembangunanUnit.upahDestroy');

        Route::post('pembangunan-unit/{id}/create-servis', [PembangunanUnitController::class, 'createServis'])
            ->name('produksi.pembangunanUnit.createServis');
    });

    // Persetujuan Upah (Shared across properti/kontraktor/kawasan approvals)
    Route::get('persetujuan-upah', [PersetujuanUpahController::class, 'index'])
        ->middleware('can:produksi.manajemen-upah.upah-borongan.read')
        ->name('produksi.persetujuanUpah.index');
    Route::patch('persetujuan-upah/{id}/update-status', [PersetujuanUpahController::class, 'update'])
        ->middleware('can:produksi.manajemen-upah.upah-borongan.confirm')
        ->name('produksi.persetujuanUpah.update');

    // Laporan
    Route::get('/pembangunan-unit/{id}/laporan-upah/{qcId?}', [TerminController::class, 'laporanUpah'])
        ->name('produksi.pembangunanUnit.laporanUpah');
    Route::get('/pembangunan-unit/{id}/laporan-bahan/{qcId?}', [TerminController::class, 'laporanBahan'])
        ->name('produksi.pembangunanUnit.laporanBahan');
    Route::get('/pembangunan-unit/{id}/laporan-termin/export', [TerminController::class, 'exportLaporanTermin'])
        ->name('produksi.pembangunanUnit.laporanTermin.export');

    Route::get('/pembangunan-proyek/{id}/laporan-termin/export', [TerminController::class, 'exportLaporanTerminProyek'])
        ->name('produksi.pembangunanProyek.laporanTermin.export');

    Route::get('/pembangunan-kawasan/{id}/laporan-termin/export', [TerminController::class, 'exportLaporanTerminKawasan'])
        ->name('produksi.pembangunanKawasan.laporanTermin.export');

    // Kontraktor (Pembangunan Proyek)
    Route::middleware('can:produksi.kontraktor.proyek-baru.read')->group(function () {
        Route::resource('proyek-baru', BuatPembangunanProyekController::class)->names('produksi.projectBaru');
        Route::post('proyek-baru/{id}/proses', [BuatPembangunanProyekController::class, 'proses'])->name('produksi.projectBaru.proses');
    });

    Route::middleware('can:produksi.kontraktor.pembangunan-proyek.read')->group(function () {
        Route::resource('pembangunan-proyek', PembangunanProyekController::class)->names('produksi.pembangunanProyek');
        Route::middleware('check.freeze')->group(function () {
            Route::post('pembangunan-proyek/order-barang', [PembangunanProyekController::class, 'orderStore'])->name('produksi.pembangunanProyek.orderStore');
            Route::delete('pembangunan-proyek/order-barang/{id}', [PembangunanProyekController::class, 'orderDestroy'])->name('produksi.pembangunanProyek.orderDestroy');
            Route::post('pembangunan-proyek/return-barang', [PembangunanProyekController::class, 'returnStore'])->name('produksi.pembangunanProyek.returnStore');
        });
        Route::post('pembangunan-proyek/upah-pengajuan', [PembangunanProyekController::class, 'upahStore'])->name('produksi.pembangunanProyek.upahStore');
        Route::delete('pembangunan-proyek/upah-pengajuan/{id}', [PembangunanProyekController::class, 'upahDestroy'])->name('produksi.pembangunanProyek.upahDestroy');
    });

    // Kawasan (Pembangunan Kawasan)
    Route::middleware('can:produksi.kawasan.buat-pembangunan.read')->group(function () {
        Route::resource('buat-pembangunan', BuatPembangunanKawasanController::class)->names('produksi.buatPembangunanKawasan');
        Route::post('buat-pembangunan/{id}/proses', [BuatPembangunanKawasanController::class, 'proses'])->name('produksi.buatPembangunanKawasan.proses');
    });

    Route::middleware('can:produksi.kawasan.pembangunan-kawasan.read')->group(function () {
        Route::resource('pembangunan-kawasan', PembangunanKawasanController::class)->names('produksi.pembangunanKawasan');
        Route::middleware('check.freeze')->group(function () {
            Route::post('pembangunan-kawasan/order-barang', [PembangunanKawasanController::class, 'orderStore'])->name('produksi.pembangunanKawasan.orderStore');
            Route::delete('pembangunan-kawasan/order-barang/{id}', [PembangunanKawasanController::class, 'orderDestroy'])->name('produksi.pembangunanKawasan.orderDestroy');
            Route::post('pembangunan-kawasan/return-barang', [PembangunanKawasanController::class, 'returnStore'])->name('produksi.pembangunanKawasan.returnStore');
        });
        Route::post('pembangunan-kawasan/upah-pengajuan', [PembangunanKawasanController::class, 'upahStore'])->name('produksi.pembangunanKawasan.upahStore');
        Route::delete('pembangunan-kawasan/upah-pengajuan/{id}', [PembangunanKawasanController::class, 'upahDestroy'])->name('produksi.pembangunanKawasan.upahDestroy');
    });

    // Persetujuan Upah Spesifik
    Route::patch('persetujuan-upah-properti/update-status', [PersetujuanUpahPropertiController::class, 'update'])->middleware('can:produksi.manajemen-upah.upah-borongan.confirm');
    Route::resource('persetujuan-upah-properti', PersetujuanUpahPropertiController::class)->middleware('can:produksi.manajemen-upah.upah-borongan.read')->only(['index', 'update'])->names('produksi.persetujuanUpahProperti');
    Route::resource('persetujuan-upah-kontraktor', PersetujuanUpahKontraktorController::class)->middleware('can:produksi.manajemen-upah.upah-borongan.read')->only(['index', 'update'])->names('produksi.persetujuanUpahKontraktor');
    Route::resource('persetujuan-upah-kawasan', PersetujuanUpahKawasanController::class)->middleware('can:produksi.manajemen-upah.upah-borongan.read')->only(['index', 'update'])->names('produksi.persetujuanUpahKawasan');
});

Route::middleware(['auth', 'can:keuangan.upah-borongan.manager.read'])->prefix('manager')->group(function () {
    Route::patch('persetujuan-upah-properti/update-status', [App\Http\Controllers\Manager\PersetujuanUpahPropertiController::class, 'update']);
    Route::resource('persetujuan-upah-properti', App\Http\Controllers\Manager\PersetujuanUpahPropertiController::class)->only(['index', 'update'])->names('manager.persetujuanUpahProperti');
    Route::resource('persetujuan-upah-kontraktor', App\Http\Controllers\Manager\PersetujuanUpahKontraktorController::class)->only(['index', 'update'])->names('manager.persetujuanUpahKontraktor');
    Route::resource('persetujuan-upah-kawasan', App\Http\Controllers\Manager\PersetujuanUpahKawasanController::class)->only(['index', 'update'])->names('manager.persetujuanUpahKawasan');
});

Route::middleware(['auth', 'can:keuangan.upah-borongan.akuntan.read'])->prefix('akuntan')->group(function () {
    Route::patch('persetujuan-upah-properti/update-status', [App\Http\Controllers\Akuntan\PersetujuanUpahPropertiController::class, 'update']);
    Route::resource('persetujuan-upah-properti', App\Http\Controllers\Akuntan\PersetujuanUpahPropertiController::class)->only(['index', 'update'])->names('akuntan.persetujuanUpahProperti');
    Route::resource('persetujuan-upah-kontraktor', App\Http\Controllers\Akuntan\PersetujuanUpahKontraktorController::class)->only(['index', 'update'])->names('akuntan.persetujuanUpahKontraktor');
    Route::resource('persetujuan-upah-kawasan', App\Http\Controllers\Akuntan\PersetujuanUpahKawasanController::class)->only(['index', 'update'])->names('akuntan.persetujuanUpahKawasan');
});
Route::middleware('auth')->prefix('kpi')->group(function () {
    Route::resource('komponen', KpiKomponenController::class)->names('kpi.komponen');
    Route::resource('user', KpiUserController::class)->names('kpi.user');
    Route::get('/kpi-review', [KpiReviewController::class, 'index'])->name('kpi.review.index');
    Route::get('/request-review/{id}', [KpiReviewController::class, 'sendNotif'])->name('kpi.request.review');
    Route::put('/kpi-review/{id}', [KpiReviewController::class, 'update'])->name('kpi.review.update');
    Route::get('/kpi-review/{id}/edit', [KpiReviewController::class, 'edit'])->name('kpi.review.edit');
    Route::get('/dashboard', [KpiDashboardController::class, 'index'])->name('kpi.dashboard.index');
});

Route::get('/kpi-user/get-role-data/{roleId}', [KpiUserController::class, 'getRoleData'])->name('kpi.user.getRoleData');
Route::get('/kpi-user/{id}/export-excel', [KpiExportController::class, 'exportById'])->name('kpi.user.exportExcel');
Route::post('kpi/user/export', [KpiExportController::class, 'export'])->name('kpi.user.export');
Route::get('/kpi/dashboard/export', [KpiDashboardController::class, 'exportExcel'])->name('kpi.dashboard.export');

Route::middleware('auth')->prefix('superadmin')->group(function () {
    Route::post('role-hakakses/{id}/duplicate', [RoleHakAksesController::class, 'duplicate'])->name('superadmin.roleHakAkses.duplicate');
    Route::resource('role-hakakses', RoleHakAksesController::class)->names('superadmin.roleHakAkses');
    Route::resource('akun-karyawan', AkunKaryawanController::class)->names('superadmin.akunKaryawan');
    Route::resource('devisi', DevisiController::class)->names('superadmin.devisi');
    Route::resource('karyawan', KaryawanController::class)->names('superadmin.karyawan');
});
