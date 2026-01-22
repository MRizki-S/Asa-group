<?php
namespace App\Http\Controllers\Etalase;

use App\Models\Type;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class PerubahaanHargaTypeUnitController extends Controller
{

    protected NotificationGroupService $notificationGroup;

    // Notifikasi Group
    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    // perumahaan yang aktif saat inii
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function index()
    {
        $perumahaanId = $this->currentPerumahaanId();

        $types = Type::query()
            ->select([
                    'id',
                    'perumahaan_id',
                    'nama_type',
                    'slug',
                    'harga_dasar',
                    'harga_diajukan',
                    'status_pengajuan',
                    'tanggal_pengajuan',
                    'diajukan_oleh',
                ])
            ->with([
                    'diajukanOleh:id,username',
                    'perumahaan:id,nama_perumahaan',
                ])
            ->where('status_pengajuan', 'pending')
            ->when(
                $perumahaanId,
                fn($q) =>
                $q->where('perumahaan_id', $perumahaanId)
            )
            ->orderBy('tanggal_pengajuan', 'asc')
            ->get();

        $namaPerumahaan = null;
        if ($perumahaanId) {
            $firstType = $types->first();
            $namaPerumahaan = $firstType?->perumahaan?->nama_perumahaan;
        }

        // dd($types);
        return view('Etalase.perubahaan-harga.harga-tipe-unit.index', [
            'types' => $types,
            'breadcrumbs' => [
                [
                    'label' => 'Pengajuan Perubahan Harga Tipe Unit - ' . ($namaPerumahaan ?? 'Semua Perumahaan'),
                    'url' => route('perubahan-harga.tipe-unit.index'),
                ],
            ],
        ]);
    }

    public function tolakPengajuan(Request $request, $id)
    {
        $type = Type::findOrFail($id);

        // Cegah penolakan ulang
        if ($type->status_pengajuan !== 'pending') {
            return back()->withErrors([
                'error' => 'Pengajuan ini sudah diproses.',
            ]);
        }

        $hargaDiajukan = $type->harga_diajukan;
        $pengaju = optional($type->diajukanOleh)->nama_lengkap;

        // Update status
        $type->update([
            'harga_diajukan' => null,
            'status_pengajuan' => 'tolak',
            'disetujui_oleh' => auth()->id(),
            'tanggal_acc' => now(),
            'catatan_penolakan' => $request->catatan_penolakan ?? null,
        ]);

        // Notifikasi Group Wa
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

        $message =
            "❌ Pengajuan perubahan harga DITOLAK\n" .
            "```\n" .
            "Perumahaan     : {$type->perumahaan->nama_perumahaan}\n" .
            "Type           : {$type->nama_type}\n" .
            "Harga diajukan : Rp " . number_format($hargaDiajukan, 0, ',', '.') . "\n" .
            "Diajukan oleh  : {$pengaju}\n" .
            "Ditolak oleh   : " . auth()->user()->nama_lengkap . "\n" .
            "Status         : DITOLAK\n" .
            "```\n";

        // Optional: tampilkan alasan penolakan
        if ($type->catatan_penolakan) {
            $message .= "\n📝 Alasan Penolakan:\n{$type->catatan_penolakan}";
        }

        $this->notificationGroup->send($groupId, $message);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan perubahan harga berhasil ditolak.');
    }


    public function approvePengajuan($id)
    {
        DB::transaction(function () use ($id, &$type, &$hargaDasarLama, &$hargaDasarBaru) {

            $type = Type::lockForUpdate()->findOrFail($id);

            // Validasi
            if ($type->status_pengajuan !== 'pending' || !$type->harga_diajukan) {
                abort(403, 'Pengajuan tidak valid');
            }

            $hargaDasarLama = $type->harga_dasar;
            $hargaDasarBaru = $type->harga_diajukan;

            // 1️⃣ Update TYPE
            $type->update([
                'harga_dasar' => $hargaDasarBaru,
                'harga_diajukan' => null,
                'status_pengajuan' => 'acc',
                'disetujui_oleh' => auth()->id(),
                'tanggal_acc' => now(),
            ]);

            // 2️⃣ Update UNIT (READY saja)
            $units = Unit::where('perumahaan_id', $type->perumahaan_id)
                ->where('type_id', $type->id)
                ->where('status_unit', 'available')
                ->lockForUpdate()
                ->get();

            foreach ($units as $unit) {
                $unit->update([
                    'harga_final' => $unit->harga_final
                        - $hargaDasarLama
                        + $hargaDasarBaru,
                ]);
            }
        });

        //  NOTIFIKASI GROUP Dukungan dan Layanan
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

        $message =
            "✅ Pengajuan perubahan harga Type Unit DISSETUJUI\n" .
            "```\n" .
            "Perumahaan     : {$type->perumahaan->nama_perumahaan}\n" .
            "Type           : {$type->nama_type}\n" .
            "Harga lama     : Rp " . number_format($hargaDasarLama, 0, ',', '.') . "\n" .
            "Harga baru     : Rp " . number_format($hargaDasarBaru, 0, ',', '.') . "\n" .
            "Disetujui oleh : " . Auth::user()->nama_lengkap . "\n" .
            "Status         : ACC\n" .
            "```\n\n" .
            "📌 Harga unit READY telah diperbarui otomatis.";

        $this->notificationGroup->send($groupId, $message);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan perubahan harga berhasil disetujui dan diterapkan ke unit READY.');
    }

}
