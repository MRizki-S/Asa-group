@extends('layouts.app')

@section('pageActive', 'FeeAgen')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    {{-- Breadcrumb --}}
    <div x-data="{ pageName: 'FeeAgen' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/20 dark:text-green-300 dark:border-green-800 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm dark:bg-red-900/20 dark:text-red-300 dark:border-red-800 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Error validasi di dalam modal --}}
    @if($errors->any())
    <div class="p-3 rounded-lg bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800">
        <p class="font-semibold text-red-700 dark:text-red-400 mb-1">Terjadi kesalahan:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li class=" text-red-600 dark:text-red-400">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- Header + Tombol --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Master Fee Agen</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola dan ajukan fee untuk agen properti</p>
        </div>
        @can('master-agen.fee-agen.pengajuan')
            <button data-modal-target="modal-create" data-modal-toggle="modal-create"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Fee Baru
            </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ============ Kolom Kiri (Fee Aktif + Pending) ============ --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- === Fee Aktif === --}}
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white/80">Fee Aktif</h3>
                    <span class="ml-auto text-xs text-gray-400">{{ $feeAktif->count() }} data</span>
                </div>

                @if($feeAktif->isEmpty())
                <div class="px-4 py-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada fee agen aktif.</p>
                </div>
                @else
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($feeAktif as $fee)
                    <div class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                        {{-- Icon --}}
                        <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ $fee->judul_fee }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                Oleh: {{ $fee->pengaju->nama_lengkap ?? '-' }}
                                @if($fee->updated_at) &bull; {{ $fee->updated_at->format('d M Y') }} @endif
                            </p>
                        </div>
                        {{-- Nominal + Aksi --}}
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($fee->nominal, 0, ',', '.') }}</p>
                            <span class="inline-block text-xs px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-medium">Aktif</span>
                        </div>
                        {{-- Tombol Nonaktifkan --}}
                        @can('master-agen.fee-agen.nonaktif')
                            <form action="{{ route('marketing.feeAgen.nonAktif', $fee) }}" method="POST" class="nonaktif-fee-form flex-shrink-0">
                                @csrf @method('PATCH')
                                <button type="button" title="Nonaktifkan" class="btn-nonaktif-fee w-7 h-7 rounded-lg bg-gray-100 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-red-900/30 text-gray-400 hover:text-red-600 dark:hover:text-red-400 flex items-center justify-center transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- === Fee Pending === --}}
            <div class="rounded-xl border border-amber-200 dark:border-amber-800/60 bg-white dark:bg-white/[0.03]">
                <div class="px-4 py-3 border-b border-amber-100 dark:border-amber-800/60 bg-amber-50/50 dark:bg-amber-900/10 flex items-center gap-2 rounded-t-xl">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse flex-shrink-0"></span>
                    <h3 class="text-sm font-semibold text-amber-700 dark:text-amber-400">Menunggu Persetujuan</h3>
                    <span class="ml-auto text-xs text-amber-500">{{ $feePending->count() }} pengajuan</span>
                </div>

                @if($feePending->isEmpty())
                <div class="px-4 py-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada pengajuan pending saat ini.</p>
                </div>
                @else
                <div class="divide-y divide-amber-100 dark:divide-amber-900/30">
                    @foreach($feePending as $fee)
                    <div class="px-4 py-3 flex items-center gap-3 hover:bg-amber-50/30 dark:hover:bg-amber-900/10 transition-colors">
                        {{-- Icon --}}
                        <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ $fee->judul_fee }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                Oleh: {{ $fee->pengaju->nama_lengkap ?? '-' }}
                                @if($fee->created_at) &bull; {{ $fee->created_at->format('d M Y') }} @endif
                            </p>
                        </div>
                        {{-- Nominal --}}
                        <div class="text-right flex-shrink-0 mr-2">
                            <p class="text-sm font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($fee->nominal, 0, ',', '.') }}</p>
                            <span class="inline-block text-xs px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 font-medium">Pending</span>
                        </div>
                        {{-- Aksi --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @can('master-agen.fee-agen.aksi-pengajuan')
                                <form action="{{ route('marketing.feeAgen.approve', $fee) }}" method="POST" class="approve-fee-form">
                                    @csrf @method('PATCH')
                                    <button type="button" title="Setujui" class="btn-acc-fee w-7 h-7 rounded-lg bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-800/50 text-green-700 dark:text-green-400 flex items-center justify-center transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                                <form action="{{ route('marketing.feeAgen.reject', $fee) }}" method="POST" class="reject-fee-form">
                                    @csrf @method('DELETE')
                                    <button type="button" title="Tolak" class="btn-reject-fee w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-800/50 text-red-700 dark:text-red-400 flex items-center justify-center transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            @endcan
                            @can('master-agen.fee-agen.cancel-pengajuan')
                                <form action="{{ route('marketing.feeAgen.cancel', $fee) }}" method="POST" class="cancel-fee-form">
                                    @csrf @method('DELETE')
                                    <button type="button" title="Batalkan" class="btn-cancel-fee w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-400 flex items-center justify-center transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

        {{-- ============ Kolom Kanan: Riwayat Ditolak ============ --}}
        <div class="xl:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] sticky top-6">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white/80">Riwayat Ditolak</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800 max-h-[480px] overflow-y-auto">
                    @forelse($historyFee as $hist)
                    <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                        <div class="flex items-start justify-between gap-2 mb-0.5">
                            <p class="text-xs font-medium text-gray-700 dark:text-white/70 leading-tight">{{ $hist->judul_fee }}</p>
                            <span class="flex-shrink-0 text-xs px-1.5 py-0.5 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 font-medium">Tolak</span>
                        </div>
                        <p class="text-sm font-bold text-gray-600 dark:text-gray-300">Rp {{ number_format($hist->nominal, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            {{ $hist->pengaju->nama_lengkap ?? '-' }}
                            @if($hist->updated_at) &bull; {{ $hist->updated_at->format('d M Y') }} @endif
                        </p>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada riwayat.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- === Modal Ajukan Fee Baru === --}}
<div id="modal-create" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-[999] flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="relative w-full max-w-md p-4">
        <div class="relative bg-white rounded-2xl shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Ajukan Fee Agen Baru</h3>
                <button type="button" data-modal-hide="modal-create"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg p-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('marketing.feeAgen.store') }}" method="POST" class="p-4 space-y-4">
                @csrf
                <div>
                    <label for="judul_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Judul / Keterangan Fee <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="judul_fee" id="judul_fee" required
                        placeholder="Contoh: Fee Agen Standar 2025"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
                </div>
                <div x-data="{
                    raw: '',
                    display: '',
                    updateDisplay(e) {
                        let num = e.target.value.replace(/\D/g, '');
                        this.display = num ? num.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                        this.raw = num;
                    }
                }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nominal Fee (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium">Rp</span>
                        <input type="text" x-model="display" @input="updateDisplay" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
                        <input type="hidden" name="nominal" :value="raw" />
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" data-modal-hide="modal-create"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors">
                        Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-acc-fee')) {
            const form = e.target.closest('.approve-fee-form');
            Swal.fire({
                title: 'Setujui fee ini?',
                text: 'Fee akan langsung aktif setelah disetujui.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(r => {
                if (r.isConfirmed) form.submit();
            });
        }
        if (e.target.closest('.btn-reject-fee')) {
            const form = e.target.closest('.reject-fee-form');
            Swal.fire({
                title: 'Tolak pengajuan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Tolak!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(r => {
                if (r.isConfirmed) form.submit();
            });
        }
        if (e.target.closest('.btn-cancel-fee')) {
            const form = e.target.closest('.cancel-fee-form');
            Swal.fire({
                title: 'Batalkan pengajuan?',
                text: 'Data pengajuan akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Tidak'
            }).then(r => {
                if (r.isConfirmed) form.submit();
            });
        }
        if (e.target.closest('.btn-nonaktif-fee')) {
            const form = e.target.closest('.nonaktif-fee-form');
            Swal.fire({
                title: 'Nonaktifkan fee ini?',
                text: 'Fee yang dinonaktifkan tidak akan berlaku lagi.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Nonaktifkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(r => {
                if (r.isConfirmed) form.submit();
            });
        }
    });
</script>
@endsection