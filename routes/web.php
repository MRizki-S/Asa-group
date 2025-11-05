<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Etalase\BlokController;
use App\Http\Controllers\Etalase\EtalaseJsonController;
use App\Http\Controllers\Etalase\KualifikasiBlokController;
use App\Http\Controllers\Etalase\PerumahaanController;
use App\Http\Controllers\Etalase\TahapController;
use App\Http\Controllers\Etalase\TahapKualifikasiController;
use App\Http\Controllers\Etalase\TahapTypeController;
use App\Http\Controllers\Etalase\TypeController;
use App\Http\Controllers\Etalase\UnitController;
use App\Http\Controllers\Marketing\AkunUserController;
use App\Http\Controllers\Marketing\KelengkapanBerkasCashController;
use App\Http\Controllers\Marketing\KelengkapanBerkasKprController;
use App\Http\Controllers\Marketing\ManagePemesananController;
use App\Http\Controllers\Marketing\PemesananUnitController;
use App\Http\Controllers\Marketing\PengajuanPembatalanController;
use App\Http\Controllers\Marketing\PengajuanPemesananController;
use App\Http\Controllers\Marketing\PindahUnitController;
use App\Http\Controllers\Marketing\SettingBonusCashController;
use App\Http\Controllers\Marketing\SettingCaraBayarController;
use App\Http\Controllers\Marketing\SettingKeterlambatanController;
use App\Http\Controllers\marketing\SettingMutuPpjbController;
use App\Http\Controllers\Marketing\SettingPembatalanController;
use App\Http\Controllers\marketing\SettingPpjbController;
use App\Http\Controllers\Marketing\SettingPpjbJsonController;
use App\Http\Controllers\marketing\SettingPromoPpjbController;
use App\Http\Controllers\PerumahaanSelectController;
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
    return view('dashboard.dashboard');
})->middleware('auth');

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
Route::middleware('auth')
    ->prefix('etalase')->group(function () {

    Route::resource('perumahaan', controller: PerumahaanController::class);
    // nested resource create, store, edit, update, destroy untuk Tahap
    Route::get('perumahaan/{perumahaan:slug}/tahap/create', [TahapController::class, 'create'])
        ->name('tahap.create');
    Route::post('perumahaan/{perumahaan:slug}/tahap', [TahapController::class, 'store'])
        ->name('tahap.store');
    Route::get('perumahaan/{perumahaan:slug}/tahap/{tahap:slug}/edit',
        [TahapController::class, 'edit'])->withoutScopedBindings()->name('tahap.edit');
    Route::put('perumahaan/{perumahaan:slug}/tahap/{tahap:slug}',
        [TahapController::class, 'update'])->withoutScopedBindings()->name('tahap.update');
    Route::delete('perumahaan/{perumahaan:slug}/tahap/{tahap:slug}',
        [TahapController::class, 'destroy'])->withoutScopedBindings()->name('tahap.destroy');

    // tahap type
    Route::post('tahapType/{tahap}', [TahapTypeController::class, 'store'])->name('tahapType.store');
    Route::delete('tahapType/{id}', [TahapTypeController::class, 'destroy'])->name('tahapType.destroy');

    // tahap kualifikasi blok
    Route::post('tahapKualifikasi/{tahap}', [TahapKualifikasiController::class, 'store'])->name('tahapKualifikasi.store');
    Route::put('tahapKualifikasi/{id}', [TahapKualifikasiController::class, 'update'])->name('tahapKualifikasi.update');
    Route::delete('tahapKualifikasi/{id}', [TahapKualifikasiController::class, 'destroy'])->name('tahapKualifikasi.destroy');

    Route::resource('tipe-unit', TypeController::class)->names('tipe-unit');
    Route::get('/tipe-unit/search', [TypeController::class, 'search'])->name('tipe-unit.search');

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
        Route::get('/pemesanan/pindah-unit/{id}p', [PindahUnitController::class, 'createPengajuan'])
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


    // ⚙️ SETTING PPJB ROUTES
    Route::prefix('setting')->name('settingPPJB.')->group(function () {
        // 🏠 Halaman utama setting
        Route::get('/', [SettingPpjbController::class, 'listSettingPPJB'])
            ->name('index');

        // 💰 PROMO (Cash & KPR)
        Route::prefix('promo')->name('promo.')->group(function () {
            // Promo Cash
            Route::get('cash/edit', [SettingPromoPpjbController::class, 'editCash'])->name('promoCash.edit');
            Route::post('cash', [SettingPromoPpjbController::class, 'updateCash'])->name('promoCash.pengajuanUpdate');

            // Promo KPR
            Route::get('kpr/edit', [SettingPromoPpjbController::class, 'editKpr'])->name('promoKpr.edit');
            Route::post('kpr', [SettingPromoPpjbController::class, 'updateKpr'])->name('promoKpr.pengajuanUpdate');

            // Riwayat promo
            Route::get('{type}/history', [SettingPromoPpjbController::class, 'history'])
                ->whereIn('type', ['cash', 'kpr'])
                ->name('history');

            // Approval & Tolak promo
            Route::patch('{promoBatch}/approve', [SettingPromoPpjbController::class, 'approvePengajuan'])->name('approve');
            Route::delete('{promoBatch}/reject', [SettingPromoPpjbController::class, 'rejectPengajuan'])->name('reject');

            // Batalkan & Nonaktifkan promo
            Route::delete('{batch}', [SettingPromoPpjbController::class, 'cancelPengajuanPromo'])->name('pengajuanCancel');
            Route::patch('{batch}/nonAktif', [SettingPromoPpjbController::class, 'nonAktifPromo'])->name('nonAktif');
        });

        // 🧱 MUTU PPJB
        Route::prefix('mutu')->name('mutu.')->group(function () {
            Route::get('edit', [SettingMutuPpjbController::class, 'edit'])->name('edit');
            Route::post('pengajuan-update', [SettingMutuPpjbController::class, 'pengajuanUpdate'])->name('pengajuanUpdate');
            Route::patch('{batch}/nonaktif', [SettingMutuPpjbController::class, 'nonAktifMutu'])->name('nonAktif');
            Route::delete('{batch}/cancel', [SettingMutuPpjbController::class, 'cancelPengajuanMutu'])->name('cancel');
            Route::get('history', [SettingMutuPpjbController::class, 'history'])->name('history');
        });

        // 💵 BONUS CASH
        Route::prefix('bonus-cash')->name('bonusCash.')->group(function () {
            Route::get('edit', [SettingBonusCashController::class, 'edit'])->name('edit');
            Route::post('pengajuan-update', [SettingBonusCashController::class, 'pengajuanUpdate'])->name('pengajuanUpdate');
            Route::patch('{batch}/nonaktif', [SettingBonusCashController::class, 'nonAktif'])->name('nonAktif');
            Route::delete('{batch}/cancel', [SettingBonusCashController::class, 'cancelPengajuan'])->name('cancel');
            Route::get('history', [SettingBonusCashController::class, 'history'])->name('history');
            Route::patch('{bonusCash}/approve', [SettingBonusCashController::class, 'approvePengajuan'])->name('approve');
            Route::delete('{bonusCash}/reject', [SettingBonusCashController::class, 'rejectPengajuan'])->name('reject');
        });

        // 💳 CARA BAYAR
        Route::prefix('cara-bayar')->name('caraBayar.')->group(function () {
            Route::get('edit', [SettingCaraBayarController::class, 'editCaraBayar'])->name('edit');
            Route::post('/', [SettingCaraBayarController::class, 'updatePengajuan'])->name('updatePengajuan');
            Route::delete('{caraBayar}', [SettingCaraBayarController::class, 'cancelPengajuanCaraBayar'])->name('cancelPengajuanPromo');
            Route::patch('{caraBayar}/nonaktif', [SettingCaraBayarController::class, 'nonAktifCaraBayar'])->name('nonAktif');
            Route::patch('{caraBayar}/approve', [SettingCaraBayarController::class, 'approvePengajuanCaraBayar'])->name('approve');
            Route::delete('{caraBayar}/reject', [SettingCaraBayarController::class, 'rejectPengajuanCaraBayar'])->name('reject');
        });

        // ⏰ KETERLAMBATAN PEMBAYARAN
        Route::prefix('keterlambatan')->name('keterlambatan.')->group(function () {
            Route::get('edit', [SettingKeterlambatanController::class, 'editKeterlambatan'])->name('edit');
            Route::post('/', [SettingKeterlambatanController::class, 'updatePengajuan'])->name('updatePengajuan');
            Route::delete('{keterlambatan}', [SettingKeterlambatanController::class, 'cancelPengajuanKeterlambatan'])->name('cancelPengajuanPromo');
            Route::patch('{keterlambatan}/nonaktif', [SettingKeterlambatanController::class, 'nonAktifKeterlambatan'])->name('nonAktif');
            Route::patch('{keterlambatan}/approve', [SettingKeterlambatanController::class, 'approvePengajuan'])->name('approve');
            Route::delete('{keterlambatan}/reject', [SettingKeterlambatanController::class, 'rejectPengajuan'])->name('reject');
        });

        // ❌ PEMBATALAN
        Route::prefix('pembatalan')->name('pembatalan.')->group(function () {
            Route::get('edit', [SettingPembatalanController::class, 'editPembatalan'])->name('edit');
            Route::post('/', [SettingPembatalanController::class, 'updatePengajuan'])->name('updatePengajuan');
            Route::delete('{pembatalan}', [SettingPembatalanController::class, 'cancelPengajuanPembatalan'])->name('cancelPengajuanPromo');
            Route::patch('{pembatalan}/nonaktif', [SettingPembatalanController::class, 'nonAktifPembatalan'])->name('nonAktif');
            Route::patch('{pembatalan}/approve', [SettingPembatalanController::class, 'approvePengajuanPembatalan'])->name('approve');
            Route::delete('{pembatalan}/reject', [SettingPembatalanController::class, 'rejectPengajuanPembatalan'])->name('reject');
        });
    });

    Route::prefix('api')->group(function () {
        Route::get('/setting-cara-bayar/{perumahaanId}', [SettingPpjbJsonController::class, 'showByPerumahaan'])
            ->name('api.setting-caraBayar.show');
    });
});
