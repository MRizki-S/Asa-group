<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MasterAgent;
use Illuminate\Http\Request;

class AgenController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agenData = MasterAgent::latest()->get();
        return view('marketing.master-agen.agen.index', [
            'agenData' => $agenData,
            'breadcrumbs'     => [
                ['label' => 'Agen', 'url' => route('marketing.agen.index')],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_agent' => 'required|string|unique:master_agent,nama_agent',
            'no_hp'      => 'nullable|string',
            'alamat'     => 'nullable|string',
        ], [
            'nama_agent.required' => 'Nama agen wajib diisi',
            'nama_agent.string'   => 'Nama agen harus berupa string',
            'nama_agent.unique'   => 'Nama agen sudah ada',
        ]);

        MasterAgent::create([
            'nama_agent' => $request->nama_agent,
            'no_hp'      => $request->no_hp,
            'alamat'     => $request->alamat,
            'status'     => true,
        ]);

        return redirect()->route('marketing.agen.index')->with('success', 'Agen berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $agen = MasterAgent::findOrFail($id);

        $agen->delete();

        return redirect()->route('marketing.agen.index')->with('success','Agen berhasil dihapus');
    }
}
