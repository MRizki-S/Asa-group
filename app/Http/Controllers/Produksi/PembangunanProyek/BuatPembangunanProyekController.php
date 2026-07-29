<?php

namespace App\Http\Controllers\Produksi\PembangunanProyek;

use App\Http\Controllers\Controller;
use App\Models\PembangunanProyek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class BuatPembangunanProyekController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    protected function sendGroupNotificationProses(PembangunanProyek $project)
    {
        $project->loadMissing(['pengawas']);

        $groupId = env('FONNTE_ID_GROUP_PROSES_PROYEK');
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_proyek.proses_proyek', [
            'namaProyek'   => $project->nama ?? '-',
            'namaPengawas' => $project->pengawas->nama_lengkap ?? '-',
            'tanggal'      => now()->format('d/m/Y H:i') . ' WIB',
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
        }
    }

    public function index()
    {
        $projects = PembangunanProyek::latest()->get();
        $users = \App\Models\User::role('Pengawas Proyek Mangoon')->get();
        return view('produksi.project_baru.index', compact('projects', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'pengawas_id' => 'nullable|exists:users,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'catatan' => 'nullable|string'
        ]);

        PembangunanProyek::create($request->all());

        return redirect()->back()->with('success', 'Project baru berhasil dibuat!');
    }

    public function edit($id)
    {
        $project = PembangunanProyek::findOrFail($id);
        if ($project->status_pembangunan !== 'pending') {
            abort(403, 'Hanya project pending yang dapat diedit');
        }
        $users = \App\Models\User::role('Pengawas Proyek Mangoon')->get();
        return view('produksi.project_baru.edit', compact('project', 'users'));
    }

    public function update(Request $request, $id)
    {
        $project = PembangunanProyek::findOrFail($id);
        if ($project->status_pembangunan !== 'pending') {
            abort(403, 'Hanya project pending yang dapat diedit');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'pengawas_id' => 'nullable|exists:users,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'catatan' => 'nullable|string'
        ]);

        $project->update($request->all());

        return redirect()->route('produksi.projectBaru.index')->with('success', 'Project berhasil diupdate!');
    }

    public function destroy($id)
    {
        $project = PembangunanProyek::findOrFail($id);
        if ($project->status_pembangunan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya project pending yang dapat dihapus');
        }
        $project->delete();
        return redirect()->route('produksi.projectBaru.index')->with('success', 'Project berhasil dihapus!');
    }

    public function proses($id)
    {
        $project = PembangunanProyek::findOrFail($id);
        if ($project->status_pembangunan !== 'pending') {
            return redirect()->back()->with('error', 'Project sudah diproses');
        }

        $project->update(['status_pembangunan' => 'proses']);
        $this->sendGroupNotificationProses($project);

        return redirect()->route('produksi.projectBaru.index')->with('success', 'Project mulai diproses!');
    }
}
