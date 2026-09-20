<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RekapNotaMasukController extends Controller
{
    public function index(Request $request)
    {
        return view('gudang.daftar-nota-masuk.index');
    }
}
