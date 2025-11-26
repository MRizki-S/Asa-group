<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Adendum;
use App\Models\PemesananUnit;
use App\Models\PemesananUnitCicilan;
use App\Models\SubAdendumCaraBayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdendumController extends Controller
{

    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function index()
    {

        return view('marketing.adendum.pilihanAdendum', [
            'breadcrumbs' => [
                ['label' => 'Adendum', 'url' => route('marketing.adendum.index')],
            ],
        ]);
    }

    public function caraBayar()
    {
        $perumahaanId = $this->currentPerumahaanId();

        $customers = PemesananUnit::with([
            'customer', 'unit.type', 'perumahaan', 'tahap',
            'kpr.bank', 'cash',
            'cicilan' => fn($q) => $q->where('is_active', true)->orderBy('pembayaran_ke'),
        ])
            ->where('status_pemesanan', 'proses')
            ->when($perumahaanId, fn($q) => $q->where('perumahaan_id', $perumahaanId))
            ->where(function ($q) {
                $q->where('cara_bayar', '!=', 'kpr')
                    ->orWhere(function ($sub) {
                        $sub->where('cara_bayar', 'kpr')
                            ->whereHas('kpr', function ($k) {
                                $k->where('status_kpr', '!=', 'acc');
                            });
                    });
            })
            ->get();

        // dd($customers);
        // ---- FIX DI SINI ----
        $customersData = $customers->map(function ($c) {

            // *** Ambil default berdasarkan cara bayar ***
            $default = null;

            if ($c->cara_bayar === 'cash' && $c->cash) {
                $default = [
                    'harga_rumah'       => $c->cash->harga_rumah,
                    'luas_kelebihan'    => $c->cash->luas_kelebihan,
                    'nominal_kelebihan' => $c->cash->nominal_kelebihan,
                    'harga_jadi'        => $c->cash->harga_jadi,
                ];
            }

            if ($c->cara_bayar === 'kpr' && $c->kpr) {
                $default = [
                    'dp_rumah_induk'        => $c->kpr->dp_rumah_induk,
                    'dp_dibayarkan_pembeli' => $c->kpr->dp_dibayarkan_pembeli,
                    'sbum_dari_pemerintah'  => $c->kpr->sbum_dari_pemerintah,
                    'luas_kelebihan'        => $c->kpr->luas_kelebihan,
                    'nominal_kelebihan'     => $c->kpr->nominal_kelebihan,
                    'total_dp'              => $c->kpr->total_dp,
                    'harga_kpr'             => $c->kpr->harga_kpr,
                    'harga_total'           => $c->kpr->harga_total,
                    'bank'                  => $c->kpr->bank->nama_bank ?? null,
                ];
            }

            return [
                'id'           => $c->id,
                'nama_lengkap' => $c->customer->nama_lengkap ?? '',
                'nama_unit'    => $c->unit->nama_unit ?? '',
                'cara_bayar'   => $c->cara_bayar,

                'pemesanan'    => [
                    'perumahaan_id'   => $c->perumahaan_id,
                    'nama_perumahaan' => $c->perumahaan->nama_perumahaan ?? '',
                    'tahap_id'        => $c->tahap_id,
                    'nama_tahap'      => $c->tahap->nama_tahap ?? '',
                    'unit_id'         => $c->unit_id,
                    'nama_unit'       => $c->unit->nama_unit ?? '',
                    'type_unit'       => $c->unit->type->nama_type ?? '',
                    'total_tagihan'   => $c->total_tagihan,
                    'sisa_tagihan'    => $c->sisa_tagihan,
                ],

                'cicilan'      => $c->cicilan->map(function ($ci) {
                    return [
                        'id'            => $ci->id,
                        'pembayaran_ke' => $ci->pembayaran_ke,
                        'jatuh_tempo'   => $ci->tanggal_jatuh_tempo?->format('Y-m-d'),
                        'nominal'       => $ci->nominal,
                        'status_bayar'  => $ci->status_bayar,
                        'tanggal_bayar' => $ci->tanggal_pembayaran?->format('Y-m-d'),
                    ];
                }),

                'default_data' => $default,
            ];

        });

        // dd($customersData);
        return view('marketing.adendum.adendum-caraBayar', [
            'customersData' => $customersData, // ← pakai data yang SUDAH dibersihkan
            'breadcrumbs'   => [
                ['label' => 'Adendum', 'url' => route('marketing.adendum.index')],
                ['label' => 'Adendum Cara Bayar', 'url' => route('marketing.adendum.caraBayar')],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $jenis = $request->jenis_adendum;

        switch ($jenis) {
            case 'cara_bayar':
                return $this->handleCaraBayar($request);
            case 'ganti_unit':
            // return $this->handleGantiUnit($request)
            default:
                abort(400, 'Jenis adendum tidak dikenal');
        }
    }

    protected function handleCaraBayar(Request $request)
    {
        DB::beginTransaction();
        try {
            // dd($request->all());
            // 🔹 Validasi request
            $request->validate([
                'user_id'                        => 'required|exists:users,id',
                'pemesanan_id'                   => 'required|exists:pemesanan_unit,id',
                'tanggal_adendum'                => 'required|date',
                'cara_bayar_baru'                => 'required|string|in:kpr,cash',

                // 🔹 Validasi khusus KPR
                'kpr_dp_rumah_induk_baru'        => 'required_if:cara_bayar_baru,kpr|numeric|min:0',
                'kpr_dp_dibayarkan_pembeli_baru' => 'required_if:cara_bayar_baru,kpr|numeric|min:0',
                'kpr_sbum_dari_pemerintah_baru'  => 'required_if:cara_bayar_baru,kpr|numeric|min:0', // wajib
                'kpr_luas_kelebihan_baru'        => 'nullable',
                'kpr_nominal_kelebihan_baru'     => 'nullable|numeric|min:0',
                'kpr_total_dp_baru'              => 'required_if:cara_bayar_baru,kpr|numeric|min:0',
                'kpr_harga_total_baru'           => 'required_if:cara_bayar_baru,kpr|numeric|min:0',
                'kpr_harga_kpr_baru'             => 'required_if:cara_bayar_baru,kpr|numeric|min:0',

                // 🔹 Validasi khusus Cash
                'cash_harga_rumah'               => 'required_if:cara_bayar_baru,cash|numeric|min:0',
                'cash_luas_kelebihan'            => 'nullable',
                'cash_nominal_kelebihan'         => 'nullable|numeric|min:0',
                'cash_harga_jadi'                => 'required_if:cara_bayar_baru,cash|numeric|min:0',

                // 🔹 Validasi cicilan
                'pembayaran_ke'                  => 'required|array',
                'pembayaran_ke.*'                => 'required|integer|min:1',
                'status_bayar'                   => 'required|array',
                'status_bayar.*'                 => 'required|string|in:pending,lunas',
                'tanggal_angsuran'               => 'required|array',
                'tanggal_angsuran.*'             => 'required|date',
                'nominal_angsuran'               => 'required|array',
                'nominal_angsuran.*'             => 'required|numeric|min:0',
            ]);
            // dd('Lolos validasi mas');

            // 🔹 Ambil pemesanan unit beserta cicilan
            $pemesananUnit = PemesananUnit::with(['cicilan' => function ($query) {
                $query->where('is_active', 1)
                    ->whereNull('adendum_id'); // hanya cicilan aktif & belum terkait adendum
            }])->findOrFail($request->pemesanan_id);

            // 🔹 Tentukan cara bayar lama & data lama (sudah kamu buat)
            $caraBayarLama = $pemesananUnit->cara_bayar;
            if ($caraBayarLama === 'kpr') {
                $dataLamaMain  = $pemesananUnit->kpr; // instance PemesananUnitKpr
                $dataLamaArray = [
                    'dp_rumah_induk'        => $dataLamaMain->dp_rumah_induk,
                    'dp_dibayarkan_pembeli' => $dataLamaMain->dp_dibayarkan_pembeli,
                    'sbum_dari_pemerintah'  => $dataLamaMain->sbum_dari_pemerintah,
                    'luas_kelebihan'        => $dataLamaMain->luas_kelebihan,
                    'nominal_kelebihan'     => $dataLamaMain->nominal_kelebihan,
                    'total_dp'              => $dataLamaMain->total_dp,
                    'harga_kpr'             => $dataLamaMain->harga_kpr,
                    'harga_total'           => $dataLamaMain->harga_total,
                    // 🔹 Tambahkan data cicilan lama
                    'cicilan'               => $pemesananUnit->cicilan->map(function ($c) {
                        return [
                            'pembayaran_ke'       => $c->pembayaran_ke,
                            'status_bayar'        => $c->status_bayar,
                            'tanggal_jatuh_tempo' => $c->tanggal_jatuh_tempo?->format('Y-m-d'),
                            'nominal'             => $c->nominal,
                        ];
                    })->toArray(),
                ];
            } else {                               // cash
                $dataLamaMain  = $pemesananUnit->cash; // instance PemesananUnitCash
                $dataLamaArray = [
                    'harga_rumah'       => $dataLamaMain->harga_rumah,
                    'luas_kelebihan'    => $dataLamaMain->luas_kelebihan,
                    'nominal_kelebihan' => $dataLamaMain->nominal_kelebihan,
                    'harga_jadi'        => $dataLamaMain->harga_jadi,
                    // 🔹 Tambahkan data cicilan lama
                    'cicilan'           => $pemesananUnit->cicilan->map(function ($c) {
                        return [
                            'pembayaran_ke'       => $c->pembayaran_ke,
                            'status_bayar'        => $c->status_bayar,
                            'tanggal_jatuh_tempo' => $c->tanggal_jatuh_tempo?->format('Y-m-d'),
                            'nominal'             => $c->nominal,
                        ];
                    })->toArray(),
                ];
            }

            // 🔹 Siapkan cicilan baru dari request
            $cicilanBaru = [];
            if ($request->has('pembayaran_ke') && is_array($request->pembayaran_ke)) {
                foreach ($request->pembayaran_ke as $i => $pembayaranKe) {
                    $cicilanBaru[] = [
                        'pembayaran_ke'       => $pembayaranKe,
                        'status_bayar'        => $request->status_bayar[$i] ?? 'pending',
                        'tanggal_jatuh_tempo' => $request->tanggal_angsuran[$i] ?? null,
                        'nominal'             => $request->nominal_angsuran[$i] ?? 0,
                    ];
                }
            }
            


            // 🔹 Siapkan data baru sesuai cara_bayar_baru
            if ($request->cara_bayar_baru === 'kpr') {
                $dataBaruArray = [
                    'dp_rumah_induk'        => $request->kpr_dp_rumah_induk_baru,
                    'dp_dibayarkan_pembeli' => $request->kpr_dp_dibayarkan_pembeli_baru,
                    'sbum_dari_pemerintah'  => $request->kpr_sbum_dari_pemerintah_baru ?? 0,
                    'luas_kelebihan'        => $request->kpr_luas_kelebihan_baru ?? null,
                    'nominal_kelebihan'     => $request->kpr_nominal_kelebihan_baru,
                    'total_dp'              => $request->kpr_total_dp_baru,
                    'harga_kpr'             => $request->kpr_harga_kpr_baru,
                    'harga_total'           => $request->kpr_harga_total_baru,
                    'cicilan'               => $cicilanBaru, // ✅ masukkan cicilan di sini
                ];
            } else { // cash
                $dataBaruArray = [
                    'harga_rumah'       => $request->cash_harga_rumah,
                    'luas_kelebihan'    => $request->cash_luas_kelebihan ?? null,
                    'nominal_kelebihan' => $request->cash_nominal_kelebihan ?? null,
                    'harga_jadi'        => $request->cash_harga_jadi,
                    'cicilan'           => $cicilanBaru, // ✅ masukkan cicilan di sini
                ];
            }

            // CEK PERUBAHAAN DARI DATA YANG DIKIRIM DENGAN DATA LAMA
            // 🔹 Cek apakah cara bayar berubah
            $caraBayarBerubah = $caraBayarLama !== $request->cara_bayar_baru;

            // 🔹 Cek data utama (exclude cicilan)
            $dataUtamaBerubah = false;
            foreach ($dataLamaArray as $key => $valueLama) {
                if ($key === 'cicilan') {
                    continue;
                }
                // skip cicilan

                $valueBaru = $dataBaruArray[$key] ?? null;

                if (is_numeric($valueLama) && is_numeric($valueBaru)) {
                    // cast ke float agar 1000.00 dan 1000 dianggap sama
                    if ((float) $valueLama !== (float) $valueBaru) {
                        $dataUtamaBerubah = true;
                        break;
                    }
                } else {
                    if ($valueLama != $valueBaru) { // string / null / date
                        $dataUtamaBerubah = true;
                        break;
                    }
                }
            }

            // 🔹 Cek cicilan
            $cicilanBerubah = false;
            $cicilanLama    = $dataLamaArray['cicilan'] ?? [];
            $cicilanBaru    = $dataBaruArray['cicilan'] ?? [];

            if (count($cicilanLama) !== count($cicilanBaru)) {
                $cicilanBerubah = true;
            } else {
                foreach ($cicilanBaru as $i => $c) {
                    $lama = $cicilanLama[$i];

                    if ((int) $lama['pembayaran_ke'] !== (int) $c['pembayaran_ke'] ||
                        $lama['status_bayar'] != $c['status_bayar'] ||
                        $lama['tanggal_jatuh_tempo'] != $c['tanggal_jatuh_tempo'] ||
                        (float) $lama['nominal'] !== (float) $c['nominal']) {
                        $cicilanBerubah = true;
                        break;
                    }
                }
            }


            // dd($caraBayarLama, $request->cara_bayar_baru, $dataLamaArray, $dataBaruArray, $dataLamaArray['cicilan'], $cicilanBaru);
            // Jika tidak ada perubahan sama sekali → hentikan proses
            if (! $caraBayarBerubah && ! $dataUtamaBerubah && ! $cicilanBerubah) {
                DB::rollBack();
                return redirect()
                    ->route('marketing.adendum.caraBayar')
                    ->with('error', 'Tidak ada perubahan pada cara bayar atau cicilan. Adendum tidak dibuat.');
            }

            // 🔹 Simpan ke table utama adendm$adendum
            $adendum = Adendum::create([
                'pemesanan_unit_id' => $pemesananUnit->id,
                'jenis'             => 'cara_bayar',
                'jenis_list'        => ['cara_bayar'], // bisa array karena cast ke json
                'status'            => 'pending',      // default status
                'diajukan_oleh'     => Auth::id(),     // user login
                'disetujui_oleh'    => null,
                'tanggal_adendum'   => $request->tanggal_adendum,
            ]);
            // dd('berhasil buat adendum cara bayar', $adendum);

            // 🔹 Insert ke sub_adendum_cara_bayar
            $subAdendum = SubAdendumCaraBayar::create([
                'adendum_id'        => $adendum->id,
                'pemesanan_unit_id' => $pemesananUnit->id,
                'cara_bayar_lama'   => $caraBayarLama,
                'cara_bayar_baru'   => $request->cara_bayar_baru,
                'data_lama_json'    => $dataLamaArray,
                'data_baru_json'    => $dataBaruArray,
            ]);
            // dd('berhasil buat SUB Adendum cara bayar', $subAdendum);

            DB::commit();

            return redirect()
                ->route('marketing.adendum.caraBayar')
                ->with('success', 'Adendum cara bayar berhasil diajukan dan disimpan sebagai pending.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
