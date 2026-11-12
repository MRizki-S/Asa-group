<?php
namespace App\Providers;

use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangReturn;
use App\Models\PembangunanLainBarangOrder;
use App\Models\PembangunanLainBarangReturn;
use App\Models\PembangunanProyekBarangOrder;
use App\Models\PembangunanProyekBarangReturn;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\PembangunanUnitBarangReturn;
use App\Models\Perumahaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Superadmin bypass all gates
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Superadmin') ? true : null;
        });

        // Kirim semua perumahaan ke header
        View::composer('layouts.app', function ($view) {
            $view->with('perumahaan', Perumahaan::all());
        });

        // Badge counter untuk permintaan & retur barang (Material Proyek) yang belum di-ACC
        View::composer('partials.sidebar', function ($view) {
            $pendingOrderUnit = 0;
            $pendingOrderKawasan = 0;
            $pendingOrderProyek = 0;
            $pendingOrderLain = 0;

            $pendingReturnUnit = 0;
            $pendingReturnKawasan = 0;
            $pendingReturnProyek = 0;
            $pendingReturnLain = 0;

            if (Auth::check()) {
                $user = Auth::user();
                $isSuperadmin = $user->hasRole('Superadmin');

                // Helper untuk mendapatkan jenis_order yang boleh di-ACC oleh user saat ini
                $getAllowedOrderJenis = function (string $catPerm) use ($user, $isSuperadmin): array {
                    if ($isSuperadmin) {
                        return ['stock', 'direct', 'cash'];
                    }

                    $allowed = [];
                    if ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-stock") || $user->can('gudang.permintaan-barang.aksi-stock')) {
                        $allowed[] = 'stock';
                    }
                    if ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-direct") || $user->can('gudang.permintaan-barang.aksi-direct')) {
                        $allowed[] = 'direct';
                    }
                    if ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-cash") || $user->can('gudang.permintaan-barang.aksi-cash')) {
                        $allowed[] = 'cash';
                    }

                    return $allowed;
                };

                // Order pending counts (difilter strictly berdasarkan jenis_order yang berhak di-ACC)
                $allowedUnit = $getAllowedOrderJenis('pemb-unit');
                if (!empty($allowedUnit) && ($isSuperadmin || $user->can('gudang.permintaan-barang.pemb-unit.read'))) {
                    $pendingOrderUnit = PembangunanUnitBarangOrder::whereIn('status_order', ['diproses', 'menunggu_spv'])
                        ->whereIn('jenis_order', $allowedUnit)
                        ->count();
                }

                if ($isSuperadmin || $user->can('gudang.permintaan-barang.pemb-kawasan.read')) {
                    $allowedKawasan = $getAllowedOrderJenis('pemb-kawasan');
                    if (!empty($allowedKawasan)) {
                        $pendingOrderKawasan = PembangunanKawasanBarangOrder::whereIn('status_order', ['diproses', 'menunggu_spv'])
                            ->whereIn('jenis_order', $allowedKawasan)
                            ->count();
                    }
                }

                if ($isSuperadmin || $user->can('gudang.permintaan-barang.pemb-mangoon.read')) {
                    $allowedProyek = $getAllowedOrderJenis('pemb-mangoon');
                    if (!empty($allowedProyek)) {
                        $pendingOrderProyek = PembangunanProyekBarangOrder::whereIn('status_order', ['diproses', 'menunggu_spv'])
                            ->whereIn('jenis_order', $allowedProyek)
                            ->count();
                    }
                }

                if ($isSuperadmin || $user->can('gudang.permintaan-barang.pemb-lain.read')) {
                    $allowedLain = $getAllowedOrderJenis('pemb-lain');
                    if (!empty($allowedLain)) {
                        $pendingOrderLain = PembangunanLainBarangOrder::whereIn('status_order', ['diproses', 'menunggu_spv'])
                            ->whereIn('jenis_order', $allowedLain)
                            ->count();
                    }
                }

                // Return pending counts (hanya bagi yang memiliki hak ACC retur)
                if ($isSuperadmin || $user->can('gudang.return-barang.return-unit.aksi')) {
                    $pendingReturnUnit = PembangunanUnitBarangReturn::where('status', 'diproses')->count();
                }

                if ($isSuperadmin || $user->can('gudang.return-barang.return-kawasan.aksi')) {
                    $pendingReturnKawasan = PembangunanKawasanBarangReturn::where('status', 'diproses')->count();
                }

                if ($isSuperadmin || $user->can('gudang.return-barang.return-mangoon.aksi')) {
                    $pendingReturnProyek = PembangunanProyekBarangReturn::where('status', 'diproses')->count();
                }

                if ($isSuperadmin || $user->can('gudang.return-barang.return-lain.aksi')) {
                    $pendingReturnLain = PembangunanLainBarangReturn::where('status', 'diproses')->count();
                }
            }

            $totalPendingOrder = $pendingOrderUnit + $pendingOrderKawasan + $pendingOrderProyek + $pendingOrderLain;
            $totalPendingReturn = $pendingReturnUnit + $pendingReturnKawasan + $pendingReturnProyek + $pendingReturnLain;
            $totalPendingMaterial = $totalPendingOrder + $totalPendingReturn;

            $view->with([
                'pendingOrderUnit' => $pendingOrderUnit,
                'pendingOrderKawasan' => $pendingOrderKawasan,
                'pendingOrderProyek' => $pendingOrderProyek,
                'pendingOrderLain' => $pendingOrderLain,
                'totalPendingOrder' => $totalPendingOrder,

                'pendingReturnUnit' => $pendingReturnUnit,
                'pendingReturnKawasan' => $pendingReturnKawasan,
                'pendingReturnProyek' => $pendingReturnProyek,
                'pendingReturnLain' => $pendingReturnLain,
                'totalPendingReturn' => $totalPendingReturn,

                'totalPendingMaterial' => $totalPendingMaterial,
            ]);
        });
    }
}
