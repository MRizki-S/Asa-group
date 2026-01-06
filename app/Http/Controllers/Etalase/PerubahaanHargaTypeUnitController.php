<?php
namespace App\Http\Controllers\Etalase;

use App\Models\Type;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PerubahaanHargaTypeUnitController extends Controller
{
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
            ->when($perumahaanId, fn($q) =>
                $q->where('perumahaan_id', $perumahaanId)
            )
            ->orderBy('tanggal_pengajuan', 'asc')
            ->get();

        $namaPerumahaan = null;
        if ($perumahaanId) {
            $firstType      = $types->first();
            $namaPerumahaan = $firstType?->perumahaan?->nama_perumahaan;
        }

        // dd($types);
        return view('Etalase.perubahaan-harga.harga-tipe-unit.index', [
            'types'       => $types,
            'breadcrumbs' => [
                [
                    'label' => 'Pengajuan Perubahan Harga Tipe Unit - ' . ($namaPerumahaan ?? 'Semua Perumahaan'),
                    'url'   => route('perubahan-harga.tipe-unit.index'),
                ],
            ],
        ]);
    }

    public function tolakPengajuan($id)
    {
        $type = Type::findOrFail($id);

        // Cegah penolakan ulang
        if ($type->status_pengajuan !== 'pending') {
            return back()->withErrors([
                'error' => 'Pengajuan ini sudah diproses.',
            ]);
        }

        $type->update([
            'harga_diajukan'    => null,
            'status_pengajuan'  => 'tolak',
            'diajukan_oleh'     => null,
            'disetujui_oleh'    => null,
            'tanggal_acc'       => now(),
            'catatan_penolakan' => null, // nanti bisa dipakai kalau pakai modal textarea
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan perubahan harga berhasil ditolak.');
    }

    public function approvePengajuan($id)
    {
        DB::transaction(function () use ($id) {

            $type = Type::lockForUpdate()->findOrFail($id);

            // validasi
            if ($type->status_pengajuan !== 'pending' || ! $type->harga_diajukan) {
                abort(403, 'Pengajuan tidak valid');
            }

            $hargaDasarLama = $type->harga_dasar;
            $hargaDasarBaru = $type->harga_diajukan;

            // 1️⃣ Update TYPE
            $type->update([
                'harga_dasar'      => $hargaDasarBaru,
                'harga_diajukan'   => null,
                'status_pengajuan' => 'acc',
                'disetujui_oleh'   => auth()->id(),
                'tanggal_acc'      => now(),
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

        return redirect()
            ->back()
            ->with('success', 'Pengajuan perubahan harga berhasil disetujui dan diterapkan ke unit READY.');
    }
}
