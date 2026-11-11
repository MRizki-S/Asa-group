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

                // Order pending counts
                if ($user->can('gudang.permintaan-barang.pemb-unit.read')) {
                    $pendingOrderUnit = PembangunanUnitBarangOrder::whereIn('status_order', ['diproses', 'menunggu_spv'])->count();
                }

                if ($user->can('gudang.permintaan-barang.pemb-kawasan.read')) {
                    $pendingOrderKawasan = PembangunanKawasanBarangOrder::whereIn('status_order', ['diproses', 'menunggu_spv'])->count();
                }

                if ($user->can('gudang.permintaan-barang.pemb-mangoon.read')) {
                    $pendingOrderProyek = PembangunanProyekBarangOrder::whereIn('status_order', ['diproses', 'menunggu_spv'])->count();
                }

                if ($user->can('gudang.permintaan-barang.pemb-lain.read')) {
                    $pendingOrderLain = PembangunanLainBarangOrder::whereIn('status_order', ['diproses', 'menunggu_spv'])->count();
                }

                // Return pending counts
                if ($user->can('gudang.return-barang.return-unit.read')) {
                    $pendingReturnUnit = PembangunanUnitBarangReturn::whereIn('status', ['diproses', 'menunggu', 'menunggu_spv'])->count();
                }

                if ($user->can('gudang.return-barang.return-kawasan.read')) {
                    $pendingReturnKawasan = PembangunanKawasanBarangReturn::whereIn('status', ['diproses', 'menunggu', 'menunggu_spv'])->count();
                }

                if ($user->can('gudang.return-barang.return-mangoon.read')) {
                    $pendingReturnProyek = PembangunanProyekBarangReturn::whereIn('status', ['diproses', 'menunggu', 'menunggu_spv'])->count();
                }

                if ($user->can('gudang.return-barang.return-lain.read')) {
                    $pendingReturnLain = PembangunanLainBarangReturn::whereIn('status', ['diproses', 'menunggu', 'menunggu_spv'])->count();
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
