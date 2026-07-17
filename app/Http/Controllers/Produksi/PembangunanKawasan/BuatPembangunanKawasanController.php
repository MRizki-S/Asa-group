<?php

namespace App\Http\Controllers\Produksi\PembangunanKawasan;

use App\Http\Controllers\Controller;
use App\Models\PembangunanKawasan;
use App\Models\Perumahaan;
use Illuminate\Http\Request;
use App\Services\NotificationGroupService;

class BuatPembangunanKawasanController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    protected function sendGroupNotificationProses(PembangunanKawasan $kawasan)
    {
        $kawasan->loadMissing(['pengawas', 'perumahan']);

        $groupId = env('FONNTE_ID_GROUP_PROSES_KAWASAN');
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.proses_kawasan', [
            'namaPerumahan' => $kawasan->perumahan->nama_perumahaan ?? '-',
            'namaKawasan'   => $kawasan->nama ?? '-',
            'namaPengawas'  => $kawasan->pengawas->nama_lengkap ?? '-',
            'tanggal'       => now()->format('d/m/Y H:i') . ' WIB',
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
        }
    }

    public function index()
    {
        $kawasans = PembangunanKawasan::latest()->get();
        $perumahaans = Perumahaan::all();
        $users = \App\Models\User::role('Pengawas Kawasan')->get();
        return view('produksi.buat_pembangunan.index', compact('kawasans', 'perumahaans', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'perumahaan_id' => 'required|exists:perumahaan,id',
            'pengawas_id' => 'nullable|exists:users,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'catatan' => 'nullable|string'
        ]);

        PembangunanKawasan::create($request->all());

        return redirect()->back()->with('success', 'Pembangunan kawasan baru berhasil dibuat!');
    }

    public function edit($id)
    {
        $kawasan = PembangunanKawasan::findOrFail($id);
        if ($kawasan->status_pembangunan !== 'pending') {
            abort(403, 'Hanya kawasan pending yang dapat diedit');
        }
        $perumahaans = Perumahaan::all();
        $users = \App\Models\User::role('Pengawas Kawasan')->get();
        return view('produksi.buat_pembangunan.edit', compact('kawasan', 'perumahaans', 'users'));
    }

    public function update(Request $request, $id)
    {
        $kawasan = PembangunanKawasan::findOrFail($id);
        if ($kawasan->status_pembangunan !== 'pending') {
            abort(403, 'Hanya kawasan pending yang dapat diedit');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'perumahaan_id' => 'required|exists:perumahaan,id',
            'pengawas_id' => 'nullable|exists:users,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'catatan' => 'nullable|string'
        ]);

        $kawasan->update($request->all());

        return redirect()->route('produksi.buatPembangunanKawasan.index')->with('success', 'Kawasan berhasil diupdate!');
    }

    public function destroy($id)
    {
        $kawasan = PembangunanKawasan::findOrFail($id);
        if ($kawasan->status_pembangunan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya kawasan pending yang dapat dihapus');
        }
        $kawasan->delete();
        return redirect()->route('produksi.buatPembangunanKawasan.index')->with('success', 'Kawasan berhasil dihapus!');
    }

    public function proses($id)
    {
        $kawasan = PembangunanKawasan::findOrFail($id);
        if ($kawasan->status_pembangunan !== 'pending') {
            return redirect()->back()->with('error', 'Kawasan sudah diproses');
        }

        $kawasan->update(['status_pembangunan' => 'proses']);
        $this->sendGroupNotificationProses($kawasan);

        return redirect()->route('produksi.pembangunanKawasan.index')->with('success', 'Kawasan mulai diproses!');
    }
}
