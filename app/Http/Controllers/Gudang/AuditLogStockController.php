<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuditLogStockController extends Controller
{
    public function index(Request $request)
    {
        return view('gudang.stock-barang.index');
    }

    public function getDocDetail($refType, $refId)
    {
        return response()->json([
            'status' => 'success',
            'data' => null,
        ]);
    }
}
