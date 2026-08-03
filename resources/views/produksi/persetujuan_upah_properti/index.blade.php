@extends('layouts.app')

@section('pageActive', 'persetujuanUpah')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{
        isModalOpen: false,
        showRejectReason: false,
        rejectReason: '',
        selectedItem: null,
        selectedIds: [],
        isMultiple: false,

        openModal(item) {
            this.selectedItem = item;
            this.isMultiple = false;
            this.showRejectReason = false;
            this.rejectReason = '';
            this.isModalOpen = true;
        },
        openBatchModal() {
            if (this.selectedIds.length === 0) return;
            this.selectedItem = null;
            this.isMultiple = true;
            this.showRejectReason = false;
            this.rejectReason = '';
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
        },
        toggleSelectAll(e) {
            if (e.target.checked) {
                const checkboxes = document.querySelectorAll('.upah-checkbox:not(:disabled)');
                this.selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
            } else {
                this.selectedIds = [];
            }
        },
        confirmAction(type) {
            if (type === 'reject' && !this.rejectReason.trim()) {
                alert('Alasan penolakan wajib diisi!');
                return;
            }

            const form = document.getElementById('form-action-upah');
            const container = document.getElementById('batch-inputs-container');
            container.innerHTML = '';

            if (this.isMultiple) {
                form.action = '{{ route('produksi.persetujuanUpahProperti.update', '0') }}';
                this.selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
            } else {
                form.action = `/produksi/persetujuan-upah-properti/${this.selectedItem.id}`;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = this.selectedItem.id;
                container.appendChild(input);
            }

            document.getElementById('input-action-type').value = type;
            document.getElementById('input-alasan-hidden').value = this.rejectReason;
            form.submit();
        }
    }">

        <div x-data="{ pageName: 'Persetujuan Upah Pemb. Unit' }">
            @include('partials.breadcrumb')
        </div>
        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header & Filter --}}
                <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Daftar Pengajuan Upah Pemb. Unit
                        </h3>
                        <button type="button" x-show="selectedIds.length > 0" x-cloak @click="openBatchModal()"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition-all flex items-center gap-1.5">
                            <span>PROSES TERPILIH</span>
                            <span class="bg-white/20 text-white px-1.5 py-0.5 rounded text-[10px]" x-text="selectedIds.length"></span>
                        </button>
                    </div>

                    {{-- Filter Dropdown --}}
                    <form action="{{ route('produksi.persetujuanUpahProperti.index') }}" method="GET" id="form-filter">
                        <div class="flex items-center gap-3">
                            <label for="filter"
                                class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tampilkan:</label>
                            <select name="filter" id="filter" onchange="this.form.submit()"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-bold uppercase">
                                <option value="menunggu" {{ $filter == 'menunggu' ? 'selected' : '' }}>Menunggu
                                    Persetujuan</option>
                                <option value="disetujui" {{ $filter == 'disetujui' ? 'selected' : '' }}>Sudah Disetujui
                                </option>
                                <option value="ditolak" {{ $filter == 'ditolak' ? 'selected' : '' }}>Pengajuan Ditolak
                                </option>
                            </select>
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table id="table-upah" class="min-w-full" style="min-width: 1000px;">
                        <thead>
                            <tr>
                                <th class="w-10 bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    <input type="checkbox" @change="toggleSelectAll($event)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Unit / Pekerjaan
                                </th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tahap QC</th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-right">Budget & Akumulasi</th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-right">Nominal Diajukan</th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Status
                                </th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($allUpahPengajuan as $item)
                                @php
                                    $isFinal =
                                        $item->status_pengajuan === 'disetujui' ||
                                        $item->status_pengajuan === 'ditolak_mgr_produksi' ||
                                        $item->disetujui_mgr_produksi !== null;
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox" value="{{ $item->id }}" x-model.number="selectedIds"
                                            class="upah-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            {{ $isFinal ? 'disabled' : '' }}>
                                    </td>
                                    <td class="px-4 py-4">
                                         <div class="flex flex-col leading-tight">
                                             <span class="text-[10px] font-black text-blue-600 font-mono mb-0.5">
                                                 {{ $item->nomor_pengajuan ?? ('UBT-' . \Carbon\Carbon::parse($item->tanggal_diajukan)->format('ymd') . '-' . str_pad($item->id, 4, '0', STR_PAD_LEFT)) }}
                                             </span>
                                             <span class="text-[9px] text-gray-500 mb-0.5">
                                                 {{ \Carbon\Carbon::parse($item->tanggal_diajukan)->format('d M Y, H:i') }}
                                             </span>
                                             <span class="font-bold text-gray-900 dark:text-white uppercase">
                                                 {{ $item->pembangunanUnit->unit->nama_unit ?? '-' }}
                                             </span>
                                             <span class="text-[10px] text-emerald-600 font-bold uppercase mt-1">
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
                                    <td class="px-4 py-4 text-right font-mono">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                <span class="text-[9px] text-gray-400 font-normal uppercase mr-1">RAP:</span>Rp {{ number_format($item->rapUpah->nominal_standar ?? 0, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[11px] font-medium text-blue-600 dark:text-blue-400 mt-0.5">
                                                <span class="text-[9px] text-blue-400/80 font-normal uppercase mr-1">Akum:</span>Rp {{ number_format($item->cumulative_requested ?? 0, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-mono font-bold text-xs text-gray-700 dark:text-white">
                                        <div class="flex flex-col items-end">
                                            <span>Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}</span>
                                            @if ($item->rapUpah && $item->cumulative_requested > ($item->rapUpah->nominal_standar + 0.01))
                                                <span class="inline-block mt-1 px-1 rounded text-[7px] font-black text-red-500 uppercase bg-red-50 border border-red-100">
                                                    Melebihi RAP
                                                </span>
                                            @endif
                                        </div>

                                        @if ($item->alasan_ditolak)
                                            <div class="mt-1.5 flex justify-end">
                                                <div
                                                    class="max-w-[150px] bg-red-50 dark:bg-red-800/50 px-2 py-1 rounded border border-red-100 dark:border-red-700">
                                                    <p
                                                        class="text-[9px] text-red-500 dark:text-red-400 italic leading-tight">
                                                        <span
                                                            class="font-black uppercase text-[8px] not-italic text-red-400">Alasan:</span>
                                                        {{ $item->alasan_ditolak }}
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
                                        <div class="flex flex-wrap items-center justify-center gap-1.5">
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
                                                <div class="flex flex-col items-center justify-center gap-1">
                                                    @if ($item->status_pengajuan === 'ditolak_mgr_produksi')
                                                        <span
                                                            class="text-[9px] font-black text-red-500 uppercase tracking-tighter">Ditolak
                                                            Pada:</span>
                                                        <span
                                                            class="text-[10px] text-gray-500 font-medium italic border border-red-50 px-2 py-0.5 rounded">
                                                            {{ $item->ditolak_pada ?? '-' }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="text-[9px] font-black text-emerald-600 uppercase tracking-tighter">Disetujui
                                                            Pada:</span>
                                                        <span
                                                            class="text-[10px] text-gray-500 font-medium italic border border-emerald-50 px-2 py-0.5 rounded">
                                                            {{ $item->disetujui_mgr_produksi ?? '-' }}
                                                        </span>
                                                    @endif
                                                </div>
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

        @include('produksi.persetujuan-upah.partials.modal')

        <form id="form-action-upah" method="POST" class="hidden">
            @csrf
            @method('PATCH')
            <input type="hidden" name="action" id="input-action-type">
            <input type="hidden" name="alasan_ditolak" id="input-alasan-hidden">
            <div id="batch-inputs-container"></div>
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
