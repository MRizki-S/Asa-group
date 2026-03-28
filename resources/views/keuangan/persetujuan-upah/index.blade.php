@extends('layouts.app')

@section('pageActive', 'persetujuanUpah')

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
                    form.action = `/produksi/persetujuan-upah/${this.selectedItem.id}/update-status`;
                    document.getElementById('input-action-type').value = type;
                    document.getElementById('input-alasan-hidden').value = this.rejectReason;
                    form.submit();
                }
            });
        }
    }">

        <div x-data="{ pageName: 'Persetujuan Upah' }">
            @include('partials.breadcrumb')
        </div>
        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header & Filter --}}

                <div class="mb-4 flex items-center justify-start">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Daftar Pengajuan Upah Unit
                    </h3>


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
                                    <td class="px-4 py-4 text-center font-bold text-sm text-gray-700 dark:text-white">
                                        Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="inline-flex px-3 py-1 text-[9px] font-black uppercase rounded-full border {{ $item->status_style }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @php
                                            $isFinal =
                                                str_contains($item->status_pengajuan, 'ditolak') ||
                                                $item->status_pengajuan === 'disetujui';
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
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-4 py-1.5 rounded-lg shadow-sm transition-all active:scale-95">
                                                PROSES
                                            </button>
                                        @else
                                            <span
                                                class="text-[10px] text-gray-400 italic font-medium uppercase border border-gray-100 px-3 py-1 rounded-md">Selesai</span>
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
