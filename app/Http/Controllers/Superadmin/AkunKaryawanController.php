<?php

namespace App\Http\Controllers\Superadmin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AkunKaryawanController extends Controller
{

    public function index()
    {
        $akunKaryawan = User::with(['roles', 'perumahaan'])
            ->where('type', 'karyawan')
            ->latest()
            ->get();
        // dd($akunKaryawan);
        return view('superadmin.akun-karyawan.index', [
            'akunKaryawan' => $akunKaryawan,
            'breadcrumbs' => [
                [
                    'label' => 'Akun Karyawan',
                    'url' => route('superadmin.akunKaryawan.index'),
                ],
            ],
        ]);
    }
}
