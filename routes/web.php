<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Etalase\BlokController;
use App\Http\Controllers\Etalase\KualifikasiBlokController;
use App\Http\Controllers\Etalase\PerumahaanController;
use App\Http\Controllers\Etalase\TahapController;
use App\Http\Controllers\Etalase\TahapKualifikasiController;
use App\Http\Controllers\Etalase\TahapTypeController;
use App\Http\Controllers\Etalase\TypeController;
use App\Http\Controllers\Etalase\UnitController;
use App\Http\Controllers\Marketing\AkunUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard.dashboard');
});

// Auth
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});
Route::get('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

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
    Route::get('/perumahaan/{perumahaan:slug}/tahap-json',
        [BlokController::class, 'listByPerumahaan'])
        ->name('tahap.list'); // untuk ambil tahap sesuai perumahaan (ajax)

    Route::get('/unit', [UnitController::class, 'indexGlobal'])
        ->name('unit.indexGlobal');

    Route::prefix('{perumahaan:slug}')->group(function () {
        Route::resource('unit', UnitController::class)
            ->names('unit'); // jangan pakai except('index')
    });
    // Route::get('/etalase/perumahaan/{slug}/blok-json', [UnitController::class, 'getBlokJson']);
    // Route::get('/etalase/perumahaan/{slug}/type-json', [UnitController::class, 'getTypeJson']);
});

Route::prefix('marketing')->group(function () {
    Route::resource('/akun-user', AkunUserController::class);
});
