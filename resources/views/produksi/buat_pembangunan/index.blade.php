@extends('layouts.app')

@section('pageActive', 'buatPembangunanKawasan')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
<style>
    .select2-container--open {
        z-index: 9999999 !important;
    }
    .flatpickr-calendar {
        z-index: 9999999 !important;
    }
</style>
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{ 
    openProcessModal: false, 
    processActionUrl: '',
    kawasanNama: ''
}" x-init="$watch('openProcessModal', value => {
    if (value) {
        $nextTick(() => {
            $('#selectModalPengawas').select2({
                placeholder: 'Pilih Pengawas',
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalProcessSesi')
            });
        });
    }
})">
    @include('partials.breadcrumb', ['breadcrumbs' => [['label' => 'Buat Pembangunan Kawasan', 'url' => route('produksi.buatPembangunanKawasan.index')]]])

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ session('error') }}',
                    showConfirmButton: true
                });
            });
        </script>
    @endif

    <div class="flex flex-col gap-6 mt-6">
        <!-- Form Create -->
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Buat Pembangunan Kawasan</h3>
            <form action="{{ route('produksi.buatPembangunanKawasan.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Nama Pembangunan <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="mis. Kawasan Melati Cluster A">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Perumahan <span class="text-red-500">*</span></label>
                        <select name="perumahaan_id" id="selectPerumahan" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Pilih Perumahan</option>
                            @foreach($perumahaans as $perumahan)
                                <option value="{{ $perumahan->id }}">{{ $perumahan->nama_perumahaan }}</option>
                            @endforeach
                        </select>
                        <script>
                            $(document).ready(function() {
                                $('#selectPerumahan').select2({
                                    placeholder: "Pilih Perumahan",
                                    theme: 'bootstrap4',
                                    width: '100%'
                                });
                            });
                        </script>
                    </div>

                    <div class="md:col-span-2 flex justify-end pt-2">
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition shadow-sm">Simpan Pembangunan</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel -->
        <div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Daftar Pembangunan Kawasan</h3>
                <div class="overflow-x-auto">
                    <table id="table-pembangunan-kawasan" class="w-full text-left text-sm text-gray-500 dark:text-gray-400" style="min-width: 680px;">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Perumahan</th>
                                <th class="px-4 py-3">Riwayat Periode Pembangunan</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kawasans as $p)
                            <tr class="border-b bg-white dark:border-gray-700 dark:bg-gray-800 align-top">
                                <td class="px-4 py-4 font-bold text-gray-900 dark:text-white">{{ $p->nama }}</td>
                                <td class="px-4 py-4">{{ $p->perumahan->nama_perumahaan ?? '-' }}</td>
                                <td class="px-4 py-4">
                                    @if($p->periodes->count() > 0)
                                        <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                            @foreach($p->periodes as $index => $per)
                                                <a href="{{ route('produksi.pembangunanKawasan.show', $p->id) }}" class="block p-2 text-xs bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-700 transition cursor-pointer">
                                                    <div class="flex items-center justify-between font-bold text-gray-800 dark:text-gray-200">
                                                        <span>Sesi #{{ $p->periodes->count() - $index }}</span>
                                                        <span class="text-[9px] px-1.5 py-0.5 rounded font-black uppercase {{ $per->status == 'proses' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">{{ $per->status }}</span>
                                                    </div>
                                                    <div class="text-[10px] text-gray-500 mt-1">
                                                        <i class="fa-regular fa-calendar mr-1"></i>
                                                        {{ $per->tanggal_mulai ? \Carbon\Carbon::parse($per->tanggal_mulai)->format('d M Y') : '-' }} s/d {{ $per->tanggal_selesai ? \Carbon\Carbon::parse($per->tanggal_selesai)->format('d M Y') : 'Sekarang' }}
                                                    </div>
                                                    <div class="text-[10px] text-gray-500 mt-0.5">
                                                        <i class="fa-regular fa-user mr-1"></i>
                                                        {{ $per->pengawas->nama_lengkap ?? $per->pengawas->name ?? '-' }}
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs italic text-gray-400">Belum ada sesi pembangunan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-row items-center gap-1.5 flex-wrap">
                                        @if($p->status_pembangunan === 'pending')
                                            <button type="button"
                                                @click="openProcessModal = true; processActionUrl = '{{ route('produksi.buatPembangunanKawasan.proses', $p->id) }}'; kawasanNama = '{{ addslashes($p->nama) }}'"
                                                title="Proses Sesi"
                                                class="inline-flex items-center gap-1 text-xs font-bold text-white bg-green-600 hover:bg-green-700 px-2 py-1 rounded-md transition shadow-sm">
                                                <i class="fa-solid fa-play text-[10px]"></i> Proses
                                            </button>
                                            <a href="{{ route('produksi.buatPembangunanKawasan.edit', $p->id) }}" title="Edit" class="inline-flex items-center gap-1 text-xs font-semibold text-yellow-700 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-800/30 dark:text-yellow-300 px-2 py-1 rounded-md transition">
                                                <i class="fa-solid fa-pen text-[10px]"></i> Edit
                                            </a>
                                            <form action="{{ route('produksi.buatPembangunanKawasan.destroy', $p->id) }}" method="POST" class="inline confirm-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" title="Hapus" class="btn-delete inline-flex items-center gap-1 text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-800/30 dark:text-red-300 px-2 py-1 rounded-md transition">
                                                    <i class="fa-solid fa-trash text-[10px]"></i> Hapus
                                                </button>
                                            </form>
                                        @elseif(in_array($p->status_pembangunan, ['selesai', 'selesai dengan catatan']))
                                            <button type="button"
                                                @click="openProcessModal = true; processActionUrl = '{{ route('produksi.buatPembangunanKawasan.proses', $p->id) }}'; kawasanNama = '{{ addslashes($p->nama) }}'"
                                                title="Proses Sesi Baru"
                                                class="inline-flex items-center gap-1 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded-md transition shadow-sm">
                                                <i class="fa-solid fa-rotate-right text-[10px]"></i> Sesi Baru
                                            </button>
                                        @else
                                            <span class="text-xs font-medium text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                                Berjalan
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Proses Sesi Pembangunan -->
    <div id="modalProcessSesi" x-show="openProcessModal" x-cloak class="fixed inset-0 z-[999999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <!-- Backdrop Overlay -->
            <div x-show="openProcessModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="openProcessModal = false"></div>

            <!-- Modal Content Card (relative z-10 stays above backdrop) -->
            <div x-show="openProcessModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative z-10 w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl text-left overflow-visible shadow-2xl transform transition-all border border-gray-200 dark:border-gray-700">
                <form :action="processActionUrl" method="POST">
                    @csrf
                    <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-4 rounded-t-2xl">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white" id="modal-title">Proses Sesi Pembangunan</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="kawasanNama"></p>
                            </div>
                            <button type="button" @click="openProcessModal = false" class="text-gray-400 hover:text-gray-500">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Pengawas Kawasan <span class="text-red-500">*</span></label>
                                <select name="pengawas_id" id="selectModalPengawas" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" style="width: 100%;">
                                    <option value="">Pilih Pengawas</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->nama_lengkap ?? $user->name ?? $user->email }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                                <div class="relative" x-data="{ simpan: '{{ date('Y-m-d') }}' }">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0 2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                                    </div>
                                    <input type="text" required x-init="flatpickr($el, { dateFormat: 'd-m-Y', defaultDate: '{{ date('d-m-Y') }}', onChange: (selectedDates, dateStr, instance) => { simpan = instance.formatDate(selectedDates[0], 'Y-m-d'); } })" class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none" placeholder="Pilih Tanggal Mulai">
                                    <input type="hidden" name="tanggal_mulai" x-model="simpan">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Estimasi Tanggal Selesai</label>
                                <div class="relative" x-data="{ simpan: '' }">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0 2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                                    </div>
                                    <input type="text" x-init="flatpickr($el, { dateFormat: 'd-m-Y', onChange: (selectedDates, dateStr, instance) => { simpan = instance.formatDate(selectedDates[0], 'Y-m-d'); } })" class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none" placeholder="Pilih Tanggal Selesai">
                                    <input type="hidden" name="tanggal_selesai" x-model="simpan">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex justify-end gap-2 rounded-b-2xl">
                        <button type="button" @click="openProcessModal = false" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg shadow-sm">Mulai Proses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById("table-pembangunan-kawasan") && typeof simpleDatatables.DataTable !== 'undefined') {
            new simpleDatatables.DataTable("#table-pembangunan-kawasan", {
                searchable: true,
                sortable: true
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete')) {
            const form = e.target.closest('.confirm-delete-form');
            Swal.fire({
                title: 'Yakin hapus pembangunan ini?',
                text: "Data yang terhapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
</script>
@endsection
