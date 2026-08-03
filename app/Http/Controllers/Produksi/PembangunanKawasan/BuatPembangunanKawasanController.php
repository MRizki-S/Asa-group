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

    protected function sendGroupNotificationProses(PembangunanKawasan $kawasan, $periode = null)
    {
        $kawasan->loadMissing(['pengawas', 'perumahan']);

        $groupId = env('FONNTE_ID_GROUP_PERIODE_KAWASAN', env('FONNTE_ID_GROUP_PROSES_KAWASAN'));
        if (!$groupId) return;

        $tglMulai = $periode && $periode->tanggal_mulai ? \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d/m/Y') : ($kawasan->tanggal_mulai ? \Carbon\Carbon::parse($kawasan->tanggal_mulai)->format('d/m/Y') : '-');
        $tglSelesai = $periode && $periode->tanggal_selesai ? \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d/m/Y') : ($kawasan->tanggal_selesai ? \Carbon\Carbon::parse($kawasan->tanggal_selesai)->format('d/m/Y') : 'Sampai Selesai');

        $messageGroup = view('notifications.whatsapp.pembangunan_kawasan.periode_kawasan', [
            'namaPerumahan'  => $kawasan->perumahan->nama_perumahaan ?? '-',
            'namaKawasan'    => $kawasan->nama ?? '-',
            'namaPengawas'   => $periode->pengawas->nama_lengkap ?? $kawasan->pengawas->nama_lengkap ?? '-',
            'tanggalMulai'   => $tglMulai,
            'tanggalSelesai' => $tglSelesai,
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
        }
    }

    public function index()
    {
        $kawasans = PembangunanKawasan::with(['perumahan', 'pengawas', 'periodes.pengawas'])->latest()->get();
        $perumahaans = Perumahaan::all();
        $users = \App\Models\User::role('Pengawas Kawasan')->get();
        return view('produksi.buat_pembangunan.index', compact('kawasans', 'perumahaans', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'perumahaan_id' => 'required|exists:perumahaan,id',
        ]);

        PembangunanKawasan::create([
            'nama' => $request->nama,
            'perumahaan_id' => $request->perumahaan_id,
            'status_pembangunan' => 'pending',
        ]);

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
        ]);

        $kawasan->update($request->only(['nama', 'perumahaan_id']));

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

    public function proses(Request $request, $id)
    {
        $kawasan = PembangunanKawasan::findOrFail($id);
        if ($kawasan->status_pembangunan === 'proses') {
            return redirect()->back()->with('error', 'Sesi pembangunan untuk kawasan ini sedang berjalan');
        }

        $request->validate([
            'pengawas_id' => 'required|exists:users,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date',
        ]);

        $periode = \App\Models\PembangunanKawasanPeriode::create([
            'pembangunan_kawasan_id' => $kawasan->id,
            'pengawas_id' => $request->pengawas_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'status' => 'proses',
        ]);

        $kawasan->update([
            'status_pembangunan' => 'proses',
            'pengawas_id' => $request->pengawas_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        $this->sendGroupNotificationProses($kawasan, $periode);

        return redirect()->route('produksi.buatPembangunanKawasan.index')->with('success', 'Sesi pembangunan kawasan berhasil diproses!');
    }
}
