<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class RoleHakAksesController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();
        // dd($roles);
        return view('superadmin.role-hakAkses.index', [
            'roles' => $roles,
            'breadcrumbs' => [
                [
                    'label' => 'Role dan Hak Akses',
                    'url' => route('superadmin.roleHakAkses.index'),
                ],
            ],
        ]);
    }

    public function create() {
        return view('superadmin.role-hakAkses.create', [
            'breadcrumbs' => [
                [
                    'label' => 'Role dan Hak Akses',
                    'url' => route('superadmin.roleHakAkses.index'),
                ],
                [
                    'label' => 'Tambah Role',
                    'url' => route('superadmin.roleHakAkses.create'),
                ],
            ],
        ]);
    }
}
