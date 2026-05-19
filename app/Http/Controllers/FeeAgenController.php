<?php

namespace App\Http\Controllers;

use App\Models\MasterAgentFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeeAgenController extends Controller
{
    public function index()
    {
        // Semua fee yang sudah acc/aktif
        $feeAktif = MasterAgentFee::with(['pengaju', 'approver'])
            ->where('status_pengajuan', 'acc')
            ->latest()
            ->get();

        // Semua fee yang masih pending
        $feePending = MasterAgentFee::with(['pengaju'])
            ->where('status_pengajuan', 'pending')
            ->latest()
            ->get();

        // Riwayat yang sudah reject
        $historyFee = MasterAgentFee::with(['pengaju', 'approver'])
            ->where('status_pengajuan', 'reject')
            ->latest()
            ->take(10)
            ->get();

        return view('marketing.master-agen.fee-agen.index', [
            'feeAktif'    => $feeAktif,
            'feePending'  => $feePending,
            'historyFee'  => $historyFee,
            'breadcrumbs' => [
                ['label' => 'Fee Agen', 'url' => route('marketing.feeAgen.index')],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_fee' => 'required|string|max:255',
            'nominal'   => 'required|numeric|min:0',
        ], [
            'judul_fee.required' => 'Judul fee wajib diisi',
            'nominal.required'   => 'Nominal fee wajib diisi',
            'nominal.numeric'    => 'Nominal harus berupa angka',
            'nominal.min'        => 'Nominal tidak boleh negatif',
        ]);

        MasterAgentFee::create([
            'judul_fee'        => $request->judul_fee,
            'nominal'          => $request->nominal,
            'status_pengajuan' => 'pending',
            'diajukan_oleh'    => Auth::id(),
        ]);

        return redirect()->route('marketing.feeAgen.index')
            ->with('success', 'Pengajuan fee agen berhasil diajukan dan menunggu persetujuan.');
    }

    public function approve(MasterAgentFee $feeAgen)
    {
        if ($feeAgen->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $feeAgen->update([
            'status_pengajuan' => 'acc',
            'disetujui_oleh'   => Auth::id(),
        ]);

        return redirect()->route('marketing.feeAgen.index')
            ->with('success', 'Fee agen berhasil disetujui dan kini aktif.');
    }

    public function reject(MasterAgentFee $feeAgen)
    {
        if ($feeAgen->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $feeAgen->update([
            'status_pengajuan' => 'reject',
            'disetujui_oleh'   => Auth::id(),
        ]);

        return redirect()->route('marketing.feeAgen.index')
            ->with('success', 'Pengajuan fee agen berhasil ditolak.');
    }

    public function cancel(MasterAgentFee $feeAgen)
    {
        if ($feeAgen->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan pending yang bisa dibatalkan.');
        }

        $feeAgen->delete();

        return redirect()->route('marketing.feeAgen.index')
            ->with('success', 'Pengajuan fee agen berhasil dibatalkan.');
    }

    public function nonAktif(MasterAgentFee $feeAgen)
    {
        if ($feeAgen->status_pengajuan !== 'acc') {
            return redirect()->back()->with('error', 'Hanya fee yang aktif yang bisa dinonaktifkan.');
        }

        $feeAgen->update([
            'status_pengajuan' => 'reject',
        ]);

        return redirect()->route('marketing.feeAgen.index')
            ->with('success', 'Fee agen berhasil dinonaktifkan.');
    }
}
