<?php

namespace App\Http\Controllers\Keuangan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\KategoriAkunKeuangan;

class KategoriAkunKeuanganController extends Controller
{
    public function index()
    {
        $kategoriAkun = KategoriAkunKeuangan::all();

        return view('keuangan.kategori-akun.index', [
            'kategoriAkun' => $kategoriAkun,
            'breadcrumbs'     => [
                ['label' => 'Kategori Akun', 'url' => route('keuangan.kategoriAkun.index')],
            ],
        ]);
    }

}
