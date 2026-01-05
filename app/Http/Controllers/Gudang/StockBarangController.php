<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockBarangController extends Controller
{
    public function stockIndex()
    {
        return view('gudang.stock-barang.index', [
            'breadcrumbs' => [
                [
                    'label' => 'Stock Barang',
                    'url'   => route('gudang.stockBarang.index'),
                ],
            ],
        ]);
    }
}
