@extends('layouts.app')

@section('pageActive', 'persetujuanUpahKeuangan')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{
        isModalOpen: false,
        showRejectReason: false,
        rejectReason: '',
        selectedItem: null,
        openModal(item) {
            this.selectedItem = item;
            this.showRejectReason = false;
            this.rejectReason = '';
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
        },
        confirmAction(type) {
            if (type === 'reject' && !this.rejectReason.trim()) {
                Swal.fire('Perhatian', 'Alasan penolakan wajib diisi!', 'warning');
                return;
            }
    
            Swal.fire({
                title: 'Konfirmasi',
                text: `Apakah Anda yakin ingin ${type === 'approve' ? 'menyetujui' : 'menolak'} pengajuan ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: type === 'approve' ? '#059669' : '#dc2626',
                confirmButtonText: 'Ya, Proses'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('form-action-upah');
                    // Pastikan route action mengarah ke controller keuangan
                    form.action = `/produksi/persetujuan-upah/${this.selectedItem.id}/update-status`;
                    document.getElementById('input-action-type').value = type;
                    document.getElementById('input-alasan-hidden').value = this.rejectReason;
                    form.submit();
                }
            });
        }
    }">

        <div x-data="{ pageName: 'Persetujuan Upah Keuangan' }">
            @include('partials.breadcrumb')
        </div>

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header & Filter --}}
                <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Daftar Pengajuan Upah (Keuangan)
                    </h3>

                    <form action="{{ route('keuangan.persetujuanUpah.index') }}" method="GET" id="form-filter">
                        <div class="flex items-center gap-3">
                            <label for="filter"
                                class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status:</label>
                            <select name="filter" id="filter" onchange="this.form.submit()"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-bold uppercase">
                                <option value="menunggu" {{ $filter == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                <option value="disetujui" {{ $filter == 'disetujui' ? 'selected' : '' }}>Sudah Disetujui
                                </option>
                                <option value="ditolak" {{ $filter == 'ditolak' ? 'selected' : '' }}>Pengajuan Ditolak
                                </option>
                            </select>
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="max-w-full overflow-x-auto">
                    <table id="table-upah" class="min-w-full">
                        <thead>
                            <tr>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Unit / Pekerjaan
                                </th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tahap QC</th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    Nominal</th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Status
                                </th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($allUpahPengajuan as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col leading-tight">
                                            <span class="font-bold text-gray-900 dark:text-white uppercase">
                                                {{ $item->pembangunanUnit->unit->nama_unit ?? '-' }}
                                            </span>
                                            <span class="text-[10px] text-blue-600 font-bold uppercase mt-1">
                                                {{ $item->nama_upah }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 uppercase">
                                        <div class="text-[10px]">
                                            <p class="font-medium text-gray-700 dark:text-gray-300">
                                                {{ $item->pembangunanUnit->qcContainer->nama_container ?? '-' }}
                                            </p>
                                            <p class="text-gray-400 italic font-normal tracking-tighter">
                                                {{ $item->pembangunanUnitQc->nama_qc ?? '-' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="font-bold text-sm text-gray-700 dark:text-white leading-none">
                                            Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}
                                        </div>
                                        {{-- Catatan Pengawas ditampilkan hanya pada filter disetujui --}}
                                        @if ($item->catatan_pengawas && $filter === 'disetujui')
                                            <div class="mt-1.5 flex justify-center">
                                                <div
                                                    class="max-w-[150px] bg-gray-50 dark:bg-gray-800/50 px-2 py-1 rounded border border-gray-100 dark:border-gray-700">
                                                    <p
                                                        class="text-[9px] text-gray-500 dark:text-gray-400 italic leading-tight">
                                                        <span
                                                            class="font-black uppercase text-[8px] not-italic text-gray-400">Ket:</span>
                                                        {{ $item->catatan_pengawas }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="inline-flex px-3 py-1 text-[9px] font-black uppercase rounded-full border {{ $item->status_style }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @php
                                            /** @var \App\Models\User $user */
                                            $user = Auth::user();
                                            $isApprovedByMe = false;
                                            $myActionDate = null;
                                            $isRejected = str_contains($item->status_pengajuan, 'ditolak');

                                            // Logika Penentuan status per Role
                                            if ($user->hasRole('Manager Dukungan & Layanan')) {
                                                $isApprovedByMe = (bool) $item->disetujui_mgr_dukungan;
                                                $myActionDate = $isRejected
                                                    ? $item->ditolak_mgr_dukungan_pada
                                                    : $item->disetujui_mgr_dukungan;
                                            } elseif ($user->hasRole('Staff Akuntansi')) {
                                                $isApprovedByMe = (bool) $item->disetujui_akuntan;
                                                $myActionDate = $isRejected
                                                    ? $item->ditolak_akuntan_pada
                                                    : $item->disetujui_akuntan;
                                            } elseif ($user->hasRole('Superadmin')) {
                                                // Superadmin final jika dua-duanya sudah approve
                                                $isApprovedByMe =
                                                    $item->disetujui_mgr_dukungan && $item->disetujui_akuntan;
                                            }

                                            $isFinal = $isRejected || $isApprovedByMe;
                                        @endphp

                                        @if (!$isFinal)
                                            <button type="button"
                                                @click="openModal({
                                                    id: '{{ $item->id }}',
                                                    unit_nama: '{{ $item->pembangunanUnit->unit->nama_unit }}',
                                                    upah_nama: '{{ $item->nama_upah }}',
                                                    pengawas: '{{ $item->pembangunanUnit->pengawas->nama_lengkap ?? '-' }}',
                                                    nominal: 'Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}',
                                                    catatan: '{{ addslashes($item->catatan_pengawas) }}'
                                                })"
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold px-4 py-1.5 rounded-lg shadow-sm transition-all active:scale-95">
                                                PROSES
                                            </button>
                                        @else
                                            <div class="flex flex-col items-center justify-center gap-1">
                                                @if ($isRejected)
                                                    {{-- TAMPILAN DITOLAK --}}
                                                    <span
                                                        class="text-[9px] font-black text-red-500 uppercase tracking-tighter">Ditolak
                                                        Pada:</span>
                                                    <span
                                                        class="text-[10px] text-gray-500 font-medium italic border border-red-50 px-2 py-0.5 rounded bg-red-50/30">
                                                        {{ $myActionDate ?? '-' }}
                                                    </span>
                                                    @if ($item->alasan_ditolak && $filter === 'ditolak')
                                                        <p
                                                            class="text-[9px] text-red-400 italic leading-tight max-w-[150px] mt-1">
                                                            <strong>Alasan:</strong> {{ $item->alasan_ditolak }}
                                                        </p>
                                                    @endif
                                                @else
                                                    {{-- TAMPILAN DISETUJUI --}}
                                                    @if ($user->hasRole('Superadmin'))
                                                        <div class="flex flex-col gap-1">
                                                            <div class="flex flex-row gap-2 items-center">
                                                                <span
                                                                    class="text-[8px] font-bold text-emerald-600 uppercase">MGR:</span>
                                                                <span
                                                                    class="text-[9px] text-gray-500 italic">{{ $item->disetujui_mgr_dukungan ?? 'Belum' }}</span>
                                                            </div>
                                                            <div
                                                                class="flex flex-row gap-2 items-center border-t border-gray-100 pt-1">
                                                                <span
                                                                    class="text-[8px] font-bold text-emerald-600 uppercase">ACC:</span>
                                                                <span
                                                                    class="text-[9px] text-gray-500 italic">{{ $item->disetujui_akuntan ?? 'Belum' }}</span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="text-[9px] font-black text-emerald-600 uppercase tracking-tighter">Disetujui
                                                            Pada:</span>
                                                        <span
                                                            class="text-[10px] text-gray-500 font-medium italic border border-emerald-50 px-2 py-0.5 rounded bg-emerald-50/30">
                                                            {{ $myActionDate ?? '-' }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('produksi.persetujuan-upah.partials.modal')

        <form id="form-action-upah" method="POST" class="hidden">
            @csrf
            @method('PATCH')
            <input type="hidden" name="action" id="input-action-type">
            <input type="hidden" name="alasan_ditolak" id="input-alasan-hidden">
        </form>
    </div>

    <script>
        $(document).ready(function() {
            if (document.getElementById("table-upah") && typeof simpleDatatables.DataTable !== 'undefined') {
                new simpleDatatables.DataTable("#table-upah", {
                    searchable: true,
                    sortable: false,
                    perPage: 10
                });
            }
        });
    </script>
@endsection
