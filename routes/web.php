<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Etalase\BlokController;
use App\Http\Controllers\Etalase\TypeController;
use App\Http\Controllers\Etalase\UnitController;
use App\Http\Controllers\Etalase\TahapController;
use App\Http\Controllers\PerumahaanSelectController;
use App\Http\Controllers\Etalase\TahapTypeController;
use App\Http\Controllers\Marketing\AdendumController;
use App\Http\Controllers\Etalase\PerumahaanController;
use App\Http\Controllers\Marketing\AkunUserController;
use App\Http\Controllers\Etalase\EtalaseJsonController;
use App\Http\Controllers\Marketing\PindahUnitController;
use App\Http\Controllers\Marketing\AdendumListController;
use App\Http\Controllers\Marketing\SettingPpjbController;
use App\Http\Controllers\Etalase\KualifikasiBlokController;
use App\Http\Controllers\Marketing\PemesananUnitController;
use App\Http\Controllers\Superadmin\AkunKaryawanController;
use App\Http\Controllers\Superadmin\RoleHakAksesController;
use App\Http\Controllers\Etalase\TahapKualifikasiController;
use App\Http\Controllers\Marketing\ManagePemesananController;
use App\Http\Controllers\Marketing\SettingBonusKprController;
use App\Http\Controllers\Marketing\SettingMutuPpjbController;
use App\Http\Controllers\Marketing\SettingPpjbJsonController;
use App\Http\Controllers\Marketing\SettingBonusCashController;
use App\Http\Controllers\Marketing\SettingCaraBayarController;
use App\Http\Controllers\Marketing\SettingPromoPpjbController;
use App\Http\Controllers\Marketing\SettingPembatalanController;
use App\Http\Controllers\Marketing\PengajuanPemesananController;
use App\Http\Controllers\Marketing\PengajuanPembatalanController;
use App\Http\Controllers\Marketing\KelengkapanBerkasKprController;
use App\Http\Controllers\Marketing\SettingKeterlambatanController;
use App\Http\Controllers\Etalase\PerubahaanHargaTypeUnitController;
use App\Http\Controllers\Marketing\KelengkapanBerkasCashController;

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
Route::get('/under-development', function() {
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

    Route::get('/perumahaan/{perumahaan:slug}/tahap-json',
        [EtalaseJsonController::class, 'listByPerumahaan'])
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

    Route::resource('/akun-user', AkunUserController::class)->names('marketing.akunUser');

    Route::resource('/pemesanan-unit', PemesananUnitController::class)->names('marketing.pemesananUnit');

    // Route::resource('/manage-pemesanan', ManagePemesananController::class)->names('marketing.managePemesanan');
    Route::prefix('manage-pemesanan')->group(function () {
        // export ppjb word
        Route::get('/export/ppjbKPR/{id}', [ManagePemesananController::class, 'exportWordKPR'])
            ->name('ppjbKPR.export.word');
        Route::get('/export/ppjbCASH/{id}', [ManagePemesananController::class, 'exportWordCASH'])
            ->name('ppjbCASH.export.word');

        // 🔹 Rincian Tagihan
        Route::get('/rincian-tagihan/{id}', [ManagePemesananController::class, 'rincianTagihan'])
            ->name('marketing.rincianTagihan');

        Route::resource('/', ManagePemesananController::class)
            ->names('marketing.managePemesanan');

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
    // 🔹 Route Keputusan Project Manager
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

    Route::prefix('api')->group(function () {
        Route::get('/setting-cara-bayar/{perumahaanId}', [SettingPpjbJsonController::class, 'showByPerumahaan'])
            ->name('api.setting-caraBayar.show');
    });
});


// Superadmin Menu
Route::middleware('auth')->prefix('superadmin')->group(function() {
    // role dan hak akses
      Route::resource('role-hakakses', RoleHakAksesController::class)->names('superadmin.roleHakAkses');

    // akun karyawan
    Route::resource('akun-karyawan', AkunKaryawanController::class)->names('superadmin.akunKaryawan');
});
