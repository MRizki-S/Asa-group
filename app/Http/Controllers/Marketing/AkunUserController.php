<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\CustomerBooking;
use App\Models\Perumahaan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AkunUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function index()
    {
        // 🔹 Ambil ID perumahaan aktif dari session / user
        $currentPerumahaanId = $this->currentPerumahaanId();

        $query = User::where('type', 'customer')
            ->where('perumahaan_id', $currentPerumahaanId)
            ->with([
                'booking.unit.perumahaan',
                'booking.unit.tahap',
                'booking.unit.blok',
                'booking.sales', // biar tau siapa sales-nya
            ])
            ->latest();

        // 🔸 Filter tambahan jika login adalah Marketing atau Project Manager
        if (Auth::user()->hasAnyRole(['Marketing'])) {
            $query->whereHas('booking', function ($q) {
                $q->where('sales_id', Auth::id());
            });
        }

        $akunUser = $query->get();

        // 🔹 Ambil nama perumahaan untuk ditampilkan di breadcrumb
        $perumahaanName = null;
        if ($currentPerumahaanId) {
            $perumahaan = Perumahaan::find($currentPerumahaanId);
            $perumahaanName = $perumahaan?->nama_perumahaan;
        }

        return view('marketing.akun-user.index', [
            'akunUser' => $akunUser,
            'breadcrumbs' => [
                [
                    'label' => 'Akun User' . ($perumahaanName ? ' - ' . $perumahaanName : ''),
                    'url' => route('marketing.akunUser.index'),
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        $allPerumahaan = $user->hasGlobalAccess()
            ? Perumahaan::all()
            : Perumahaan::where('id', $user->perumahaan_id)->get();

        return view('marketing.akun-user.create', [
            'allPerumahaan' => $allPerumahaan,
            'defaultPerumahaan' => $user->perumahaan_id,
            'defaultSlug' => optional($user->perumahaan)->slug,
            'isGlobal' => $user->hasGlobalAccess(),
            'breadcrumbs' => [
                ['label' => 'Akun User', 'url' => route('marketing.akunUser.index')],
                ['label' => 'Tambah Akun User & Booking Unit', 'url' => ''],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8   ',
            'no_hp' => ['required', 'regex:/^62\d{9,13}$/'],
            'perumahaan_id' => 'required|exists:perumahaan,id',
            'tahap_id' => 'required|exists:tahap,id',
            'unit_id' => 'required|exists:unit,id',
        ], [
            // === Pesan custom ===
            'no_hp.regex' => 'Nomor HP harus diawali dengan 62 dan berisi 9-13 digit setelahnya.',
        ]);

        DB::beginTransaction();

        try {
            // 1. Buat akun user (customer)
            $user = User::create([
                'nama_lengkap' => $request->nama_lengkap,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'no_hp' => $request->no_hp,
                'type' => 'customer',        // pakai "type" sesuai model User
                'tanggal_expired' => now()->addDays(7), // akun expired 7 hari
                'perumahaan_id' => $request->perumahaan_id,
            ]);

            // 2. Buat booking
            CustomerBooking::create([
                'user_id' => $user->id,
                'perumahaan_id' => $request->perumahaan_id,
                'sales_id' => Auth::id(), // otomatis ambil sales yang login
                'tahap_id' => $request->tahap_id,
                'unit_id' => $request->unit_id,
                'slug' => Str::slug($request->username . '-' . uniqid()),
                'tanggal_booking' => now(),
                'tanggal_expired' => now()->addDays(2), // booking expired 2 hari
                'status' => 'active',
            ]);

            // 3. Update unit jadi booked
            Unit::where('id', $request->unit_id)->update([
                'status_unit' => 'booked',
            ]);

            DB::commit();

            return redirect()->route('marketing.akunUser.index')->with('success', 'Akun customer & booking berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
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
        $user = User::with([
            'booking.perumahaan',
            'booking.tahap',
            'booking.unit',
        ])->findOrFail($id);
        // dd($user);

        $dataPerumahaan = Perumahaan::all();

        return view('marketing.akun-user.edit', [
            'allPerumahaan' => $dataPerumahaan,
            'user' => $user,
            'breadcrumbs' => [
                ['label' => 'Akun User', 'url' => route('marketing.akunUser.index')],
                ['label' => 'Edit Akun User& Booking Unit', 'url' => ''],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6',
            'no_hp' => 'required|string|max:20',
            'perumahaan_id' => 'required|exists:perumahaan,id',
            'tahap_id' => 'required|exists:tahap,id',
            'unit_id' => 'required|exists:unit,id',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Ambil user & booking terkait user ini
            $user = User::findOrFail($id);
            $booking = CustomerBooking::where('user_id', $user->id)->firstOrFail();
            $oldUnitId = $booking->unit_id;

            // 2️⃣ Update data user
            $user->update([
                'username' => $request->username,
                'no_hp' => $request->no_hp,
                'perumahaan_id' => $request->perumahaan_id,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            // 3️⃣ Update data booking
            $booking->update([
                'perumahaan_id' => $request->perumahaan_id,
                'sales_id' => Auth::id(),
                'tahap_id' => $request->tahap_id,
                'unit_id' => $request->unit_id,
            ]);

            // 4️⃣ Jika unit berubah, ubah statusnya
            if ($oldUnitId != $request->unit_id) {
                // unit lama kembali available
                Unit::where('id', $oldUnitId)->update(['status_unit' => 'available']);
                // unit baru jadi booked
                Unit::where('id', $request->unit_id)->update(['status_unit' => 'booked']);
            }

            DB::commit();

            return redirect()
                ->route('marketing.akunUser.edit', $id)
                ->with('success', 'Data customer & booking berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Ambil user berdasarkan ID
            $user = User::findOrFail($id);

            // 2️⃣ Ambil booking terkait user (jika ada)
            $booking = CustomerBooking::where('user_id', $user->id)->first();

            if ($booking) {
                // 3️⃣ Update unit yang dibooking agar kembali available
                if ($booking->unit_id) {
                    Unit::where('id', $booking->unit_id)->update([
                        'status_unit' => 'available',
                    ]);
                }

                // 4️⃣ Hapus booking
                $booking->delete();
            }

            // 5️⃣ Hapus user
            $user->delete();

            DB::commit();

            // 6️⃣ Redirect kembali ke index dengan pesan sukses
            return redirect()
                ->route('marketing.akunUser.index')
                ->with('success', 'Akun user dan booking berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

}
