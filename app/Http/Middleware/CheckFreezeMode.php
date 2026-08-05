<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFreezeMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AppSetting::isFreeze()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sistem sedang dalam mode Freeze (Pencocokan Stok Opname). Seluruh transaksi barang, order, retur, dan perubahan stok sementara ditutup.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Sistem sedang dalam mode Freeze (Pencocokan Stok Opname). Seluruh transaksi barang, order, retur, dan perubahan stok sementara ditutup.');
        }

        return $next($request);
    }
}
