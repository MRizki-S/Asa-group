<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\CustomerBooking;
use App\Models\PemesananUnit;
use App\Models\PemesananUnitCaraBayar;
use App\Models\PemesananUnitCash;
use App\Models\PemesananUnitCashDokumen;
use App\Models\PemesananUnitCicilan;
use App\Models\PemesananUnitDataDiri;
use App\Models\PemesananUnitKeterlambatan;
use App\Models\PemesananUnitKpr;
use App\Models\PemesananUnitMutu;
use App\Models\PemesananUnitPembatalan;
use App\Models\PemesananUnitPromo;
use App\Models\Perumahaan;
use App\Models\PpjbCaraBayar;
use App\Models\PpjbKeterlambatan;
use App\Models\PpjbMutuBatch;
use App\Models\PpjbPembatalan;
use App\Models\PpjbPromoBatch;
use App\Models\Unit;
use App\Models\User;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PemesananUnitController extends Controller
{

    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = Auth::user();

        // Tentukan perumahaan aktif
        $currentPerumahaanId = $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;

        // Ambil booking yang masih aktif dan belum diforward ke pemesanan unit
        $query = CustomerBooking::with(['user', 'perumahaan', 'tahap', 'unit'])
            ->where('status', 'active')
            ->whereDoesntHave('user.pemesananSebagaiCustomer'); // belum punya pemesanan_unit
                                                            // ->get();

        // 🧩 Jika yang login adalah selain Super Admin → tampilkan hanya booking miliknya
        if (! $user->hasRole('Super Admin')) {
            $query->where('sales_id', Auth::id());
        }

        // Eksekusi query
        $bookings = $query->get();

        // Map data agar mudah dipakai di view
        $customersData = $bookings->map(function ($b) {
            return [
                'id'       => $b->user->id,
                'username' => $b->user->username,
                'no_hp'    => $b->user->no_hp,
                'booking'  => [
                    'id'                => $b->id,
                    'perumahaan_id'     => $b->perumahaan_id,
                    'tahap_id'          => $b->tahap_id,
                    'unit_id'           => $b->unit_id,
                    'slug'              => $b->slug,
                    'nama_perumahaan'   => $b->perumahaan->nama_perumahaan ?? '-',
                    'nama_tahap'        => $b->tahap->nama_tahap ?? '-',
                    'nama_unit'         => $b->unit->nama_unit ?? '-',
                    'harga_final'       => $b->unit->harga_final ?? 0,
                    'luas_kelebihan'    => $b->unit->luas_kelebihan,    // biarkan null
                    'nominal_kelebihan' => $b->unit->nominal_kelebihan, // biarkan null
                ],
            ];
        });

        // dd($customersData);

        // ====== 1️⃣ Keterlambatan Aktif ======
        $keterlambatan = PpjbKeterlambatan::where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->latest('id')
            ->first();

        // ====== 2️⃣ Pembatalan Aktif ======
        $pembatalan = PpjbPembatalan::where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->latest('id')
            ->first();

        // ====== 3️⃣ Promo Batch Aktif (Cash & KPR) ======
        $promoCash = PpjbPromoBatch::with(['items'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->where('tipe', 'cash')
            ->latest('id')
            ->first();

        $promoKpr = PpjbPromoBatch::with(['items'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->where('tipe', 'kpr')
            ->latest('id')
            ->first();

        return view('marketing.pemesanan-unit.create', [
            'customersData' => $customersData,
            'keterlambatan' => $keterlambatan,
            'pembatalan'    => $pembatalan,
            'promoCash'     => $promoCash,
            'promoKpr'      => $promoKpr,
            'breadcrumbs'   => [
                ['label' => 'Pemesanan Unit', 'url' => route('marketing.pemesananUnit.index')],
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
        // dd($request->all());
        // 🧩 VALIDASI SEBELUM TRANSAKSI
        $request->validate([
            // === FIELD UMUM ===
            'user_id'                   => 'required|exists:users,id',
            'tanggal_pemesanan'         => 'required|date',
            'perumahaan_id'             => 'required|exists:perumahaan,id',
            'tahap_id'                  => 'required|exists:tahap,id',
            'unit_id'                   => 'required|exists:unit,id',
            'nama_pribadi'              => 'required|string|max:255',
            'no_hp'                     => 'required',
            'no_ktp'                    => 'required',
            'pekerjaan'                 => 'required',
            'provinsi_code'             => 'required|string',
            'provinsi_nama'             => 'required|string',
            'kota_code'                 => 'required|string',
            'kota_nama'                 => 'required|string',
            'kecamatan_code'            => 'required|string',
            'kecamatan_nama'            => 'required|string',
            'desa_code'                 => 'required|string',
            'desa_nama'                 => 'required|string',
            'rt'                        => 'required|string|max:5',
            'rw'                        => 'required|string|max:5',
            'alamat_detail'             => 'required|string|max:255',
            'cara_bayar'                => 'required|in:cash,kpr',

            // === FIELD CASH (wajib jika cara_bayar = cash) ===
            'cash_harga_rumah'          => 'required_if:cara_bayar,cash|min:0',
            'cash_luas_kelebihan'       => 'required_if:cara_bayar,cash',
            'cash_nominal_kelebihan'    => 'required_if:cara_bayar,cash|numeric|min:0',
            'cash_harga_jadi'           => 'required_if:cara_bayar,cash|numeric|min:0',

            // === FIELD KPR (wajib jika cara_bayar = kpr) ===
            'kpr_dp_dibayarkan_pembeli' => 'required_if:cara_bayar,kpr|numeric|min:0',
            'kpr_dp_rumah_induk'        => 'required_if:cara_bayar,kpr|numeric|min:0',
            'kpr_luas_kelebihan'        => 'required_if:cara_bayar,kpr',
            'kpr_nominal_kelebihan'     => 'required_if:cara_bayar,kpr|numeric|min:0',
            'kpr_total_dp'              => 'required_if:cara_bayar,kpr|numeric|min:0',
            'kpr_harga_total'           => 'required_if:cara_bayar,kpr|numeric|min:0',
            'kpr_harga_kpr'             => 'required_if:cara_bayar,kpr|numeric|min:0',

            // === FIELD CICILAN ===
            'pembayaran_ke'             => 'required|array|min:1',
            'tanggal_angsuran'          => 'required|array|min:1',
            'nominal_angsuran'          => 'required|array|min:1',
            'pembayaran_ke.*'           => 'required|integer|min:1',
            'tanggal_angsuran.*'        => 'required|date',
            'nominal_angsuran.*'        => 'required|numeric|min:0',
        ], [

            'required_if' => 'Field :attribute wajib diisi jika cara bayar adalah :value.',
        ]);

        // dd($request->all());
        DB::beginTransaction();

        try {
            // Ambil user (sales) yang login
            $sales = Auth::user();

            // Ambil data unit yang dipesan
            $unit         = Unit::findOrFail($request->unit_id);
            $harga_normal = $unit->harga_final;

            // Tentukan total_tagihan dan sisa_tagihan sesuai cara bayar
            if ($request->cara_bayar === 'cash') {
                $total_tagihan = $request->cash_harga_jadi ?? 0;
            } else {
                // kpr
                $total_tagihan = $request->kpr_dp_dibayarkan_pembeli ?? 0;
            }

            $sisa_tagihan = $total_tagihan;

            // 1️⃣ Simpan data utama ke pemesanan_unit
            $pemesanan = PemesananUnit::create([
                'perumahaan_id'     => $request->perumahaan_id,
                'tahap_id'          => $request->tahap_id,
                'unit_id'           => $request->unit_id,
                'customer_id'       => $request->user_id,
                'sales_id'          => $sales->id,
                'tanggal_pemesanan' => $request->tanggal_pemesanan,
                'cara_bayar'        => $request->cara_bayar,
                'status_pengajuan'  => 'pending',
                'status_pemesanan'  => 'proses',
                'harga_normal'      => $harga_normal,
                'harga_cash'        => $request->cara_bayar === 'cash' ? $request->cash_harga_jadi : null,
                'total_tagihan'     => $total_tagihan,
                'sisa_tagihan'      => $sisa_tagihan,
            ]);

            // 2️⃣ Simpan data diri pembeli (global)
            PemesananUnitDataDiri::create([
                'pemesanan_unit_id' => $pemesanan->id,
                'nama_pribadi'      => $request->nama_pribadi,
                'no_hp'             => $request->no_hp,
                'no_ktp'            => $request->no_ktp,
                'pekerjaan'         => $request->pekerjaan,
                'provinsi_code'     => $request->provinsi_code,
                'provinsi_nama'     => $request->provinsi_nama,
                'kota_code'         => $request->kota_code,
                'kota_nama'         => $request->kota_nama,
                'kecamatan_code'    => $request->kecamatan_code,
                'kecamatan_nama'    => $request->kecamatan_nama,
                'desa_code'         => $request->desa_code,
                'desa_nama'         => $request->desa_nama,
                'rt'                => $request->rt,
                'rw'                => $request->rw,
                'alamat_detail'     => $request->alamat_detail,
            ]);

            // 3️⃣ Simpan Snapshot global untuk pemesanan_unit ini
            $this->snapshotGlobal($request, $pemesanan);

            // 4️⃣ Simpan data cicilan (jika ada)
            if ($request->has('pembayaran_ke') && is_array($request->pembayaran_ke)) {
                foreach ($request->pembayaran_ke as $index => $pembayaranKe) {
                    PemesananUnitCicilan::create([
                        'pemesanan_unit_id'   => $pemesanan->id,
                        'pembayaran_ke'       => $pembayaranKe,
                        'tanggal_jatuh_tempo' => $request->tanggal_angsuran[$index] ?? null,
                        'nominal'             => $request->nominal_angsuran[$index] ?? 0,
                        'status_bayar'        => 'pending',
                    ]);
                }
            }

            // 5️⃣ Percabangan cara bayar
            if ($request->cara_bayar === 'cash') {

                // 🔹 Simpan data ke tabel pemesanan_unit_cash
                $cash = PemesananUnitCash::create([
                    'pemesanan_unit_id' => $pemesanan->id,
                    'harga_rumah'       => $request->cash_harga_rumah,
                    'luas_kelebihan'    => $unit->luas_kelebihan ?? null,
                    'nominal_kelebihan' => $unit->nominal_kelebihan ?? null,
                    'harga_jadi'        => $request->cash_harga_jadi,
                ]);

                // 🔹 Buat daftar dokumen default
                $defaultDokumen = [
                    'KTP',
                    'KK',
                    'SKET Belum Menikah / Menikah',
                    'NPWP',
                ];

                foreach ($defaultDokumen as $namaDokumen) {
                    PemesananUnitCashDokumen::create([
                        'pemesanan_unit_cash_id' => $cash->id,
                        'nama_dokumen'           => $namaDokumen,
                        'status'                 => false, // belum dilengkapi
                        'tanggal_update'         => null,
                        'updated_by'             => null,
                    ]);
                }
            } elseif ($request->cara_bayar === 'kpr') {
                // 🔹 Simpan data ke tabel pemesanan_unit_kpr
                PemesananUnitKpr::create([
                    'pemesanan_unit_id'     => $pemesanan->id,
                    'bank_id'               => null, // sementara null
                    'dp_rumah_induk'        => $request->kpr_dp_rumah_induk,
                    'dp_dibayarkan_pembeli' => $request->kpr_dp_dibayarkan_pembeli,
                    'sbum_dari_pemerintah'  => env('SBUM_PEMERINTAH', 4000000),
                    'luas_kelebihan'        => $unit->luas_kelebihan ?? null,
                    'nominal_kelebihan'     => $unit->nominal_kelebihan ?? null,
                    'total_dp'              => $request->kpr_total_dp,
                    'harga_kpr'             => $request->kpr_harga_kpr,
                    'harga_total'           => $request->kpr_harga_total,
                    'status_kpr'            => 'proses',
                ]);
            }

            // update status unit menjadi sold
            // $unit->update(['status_unit' => 'sold']);

            // ✅ Commit transaksi
            DB::commit();

            // Kirim notifikasi ke grup WhatsApp Marketing ASA
            // Ambil group ID dari .env
            $groupId       = env('FONNTE_ID_GROUP_MARKETING_ASA');
            $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';
            $namaTahap     = $unit->tahap->nama_tahap ?? '-';
            $namaUnit      = $unit->nama_unit ?? '-';

            // Tentukan panjang maksimum label agar sejajar
            $pad = 12;

            $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';
            $namaTahap     = $unit->tahap->nama_tahap ?? '-';
            $namaUnit      = $unit->nama_unit ?? '-';

            $messageGroup =
            "🛎️ *Pengajuan Pemesanan Unit Baru*\n\n" .
            "```\n" .
            "Sales       : {$sales->nama_lengkap}\n" .
            "Customer    : {$request->nama_pribadi}\n" .
            "Perumahan   : {$namaPerumahan}\n" .
            "Tahap       : {$namaTahap}\n" .
            "Unit        : {$namaUnit}\n" .
            "Cara Bayar  : " . strtoupper($request->cara_bayar) . "\n" .
                "Status      : Pending\n" .
                "```\n\n" .
                "Menunggu persetujuan admin KPR 🕓";

            // Kirim ke group
            // if ($groupId) {
            //     $this->notificationGroup->send($groupId, $messageGroup);
            // }

            return redirect()->back()->with('success', 'Pemesanan unit berhasil dibuat. Silakan hubungi bagian KPR untuk proses persetujuan (ACC) pemesanan unit.');
        } catch (\Exception $e) {
            // ❌ Rollback jika gagal
            DB::rollBack();

            // ❌ Jika gagal
            return redirect()->back()->with('error', 'Gagal membuat pemesanan unit: ' . $e->getMessage());
        }
    }

    // snapshot data global (pemesanan_unit_promo, pemesanan_unit_keterlambatan, pemesanan_unit_pembatalan, pemesanan_unit_cara_bayar)
    private function snapshotGlobal($request, $pemesananUnit)
    {
        /**
         * ======================================
         * 🔹 SNAPSHOT PROMO (berdasarkan cara_bayar)
         * ======================================
         */
        $promoBatch = PpjbPromoBatch::with('items')
            ->where('perumahaan_id', $pemesananUnit->perumahaan_id)
            ->where('tipe', $request->cara_bayar)
            ->where('status_aktif', true)
            ->where('status_pengajuan', 'acc')
            ->first();

        if ($promoBatch && $promoBatch->items->count() > 0) {
            foreach ($promoBatch->items as $item) {
                PemesananUnitPromo::create([
                    'pemesanan_unit_id' => $pemesananUnit->id,
                    'nama_promo'        => $item->nama_promo,
                ]);
            }
        }

        /**
         * ======================================
         * 🔹 SNAPSHOT MUTU (global, tidak terikat cara_bayar)
         * ======================================
         */
        $mutuBatch = PpjbMutuBatch::with('items')
            ->where('perumahaan_id', $pemesananUnit->perumahaan_id)
            ->where('status_aktif', true)
            ->where('status_pengajuan', 'acc')
            ->first();

        if ($mutuBatch && $mutuBatch->items->count() > 0) {
            foreach ($mutuBatch->items as $item) {
                PemesananUnitMutu::create([
                    'pemesanan_unit_id' => $pemesananUnit->id,
                    'nama_mutu'         => $item->nama_mutu,
                    'nominal_mutu'      => $item->nominal_mutu,
                ]);
            }
        }

        /**
         * ======================================
         * 🔹 SNAPSHOT KETERLAMBATAN
         * ======================================
         */
        $keterlambatan = PpjbKeterlambatan::where('perumahaan_id', $pemesananUnit->perumahaan_id)
            ->where('status_aktif', true)
            ->where('status_pengajuan', 'acc')
            ->first();

        if ($keterlambatan) {
            PemesananUnitKeterlambatan::create([
                'pemesanan_unit_id' => $pemesananUnit->id,
                'persentase_denda'  => $keterlambatan->persentase_denda,
            ]);
        }

        /**
         * ======================================
         * 🔹 SNAPSHOT PEMBATALAN
         * ======================================
         */
        $pembatalan = PpjbPembatalan::where('perumahaan_id', $pemesananUnit->perumahaan_id)
            ->where('status_aktif', true)
            ->where('status_pengajuan', 'acc')
            ->first();

        if ($pembatalan) {
            PemesananUnitPembatalan::create([
                'pemesanan_unit_id'     => $pemesananUnit->id,
                'persentase_potongan'   => $pembatalan->persentase_potongan ?? null,
                'nominal_potongan_kpr'  => $pembatalan->nominal_potongan_kpr ?? null,
                'nominal_potongan_cash' => $pembatalan->nominal_potongan_cash ?? null,
            ]);
        }

        /**
         * ======================================
         * 🔹 SNAPSHOT CARA BAYAR
         * ======================================
         */
        $caraBayar = PpjbCaraBayar::where('perumahaan_id', $pemesananUnit->perumahaan_id)
            ->where('status_aktif', true)
            ->where('status_pengajuan', 'acc')
            ->first();

        if ($caraBayar) {
            PemesananUnitCaraBayar::create([
                'pemesanan_unit_id' => $pemesananUnit->id,
                'jumlah_cicilan'    => $caraBayar->jumlah_cicilan,
                'minimal_dp'        => $caraBayar->minimal_dp,
            ]);
        }

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
        //
    }
}
