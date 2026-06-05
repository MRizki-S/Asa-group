@extends('layouts.app')

@section('pageActive', 'pembangunanUnit')

@section('content')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Pembangunan Unit' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        {{-- Alert Error Validasi --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Danger</span>
                <div>
                    <span class="font-medium">Terjadi kesalahan validasi:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                {{-- tambah data --}}
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        List Pembangunan Unit
                        {{ $perumahaanSlug ? ' - ' . ucwords(str_replace('-', ' ', $perumahaanSlug)) : '' }}
                    </h3>
                </div>

                <form method="GET" action="{{ route('produksi.pembangunanUnit.index') }}"
                    class="mb-4 flex items-center gap-3" x-data="{
                        tahap: [],
                        async fetchTahap() {
                            const res = await fetch(`/etalase/perumahaan/{{ $perumahaanSlug }}/tahap-json`);
                            if (!res.ok) return;
                            this.tahap = await res.json();
                        }
                    }" x-init="fetchTahap()">
                    <h3 class="text-sm text-gray-500 dark:text-white/90">Filter -</h3>

                    <!-- Select Tahap -->
                    <div>
                        <select name="tahapFil" id="selectTahap"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-600 dark:text-white">
                            <option value="">Semua Tahap</option>
                            <template x-for="t in tahap" :key="t.id">
                                <option :value="t.slug" :selected="t.slug === '{{ $tahapSlug }}'"
                                    x-text="t.nama_tahap">
                                </option>
                            </template>
                        </select>
                        <script>
                            $(document).ready(function() {
                                $('#selectTahap').select2({
                                    placeholder: "Semua Tahap",
                                    theme: 'bootstrap4',
                                    allowClear: true,
                                    width: '100%'
                                });
                            });
                        </script>
                    </div>

                    <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Terapkan
                    </button>

                    <a href="{{ route('produksi.pembangunanUnit.index') }}"
                        class="px-4 py-2 text-sm bg-gray-200 rounded-lg hover:bg-gray-300">
                        Reset
                    </a>
                </form>

                <table id="table-pembangunan-unit">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Unit</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Tahap</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">QC</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Progres &
                                Status</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Pengawas
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Serah
                                Terima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allPembangunanUnit as $item)
                            <tr>
                                <td class="font-bold text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->unit->nama_unit }}
                                </td>
                                <td class="font-medium text-gray-600 whitespace-nowrap dark:text-gray-400 text-center">
                                    {{ $item->tahap->nama_tahap }}
                                </td>
                                <td class="font-medium text-gray-600 whitespace-nowrap dark:text-gray-400 text-center">
                                    {{ $item->qcContainer->nama_container }}
                                </td>

                                <td class="p-0 text-center" style="padding: 0 !important;">
                                    @php
                                        $bgClass = 'bg-blue-500 hover:bg-blue-600';
                                        $statusIcon = '';

                                        if ($item->status_pembangunan === 'selesai') {
                                            $bgClass = 'bg-green-500 hover:bg-green-600';
                                            $statusIcon = '<i class="fa-solid fa-circle-check mr-1"></i>';
                                        } elseif ($item->status_pembangunan === 'selesai dengan catatan') {
                                            $bgClass = 'bg-yellow-500 hover:bg-yellow-600';
                                            $statusIcon = '<i class="fa-solid fa-circle-exclamation mr-1"></i>';
                                        }
                                    @endphp

                                    <a href="{{ route('produksi.pembangunanUnit.show', $item->id) }}"
                                        class="flex flex-col items-center justify-center w-full h-full min-h-[50px] {{ $bgClass }} text-white transition-all group">
                                        <span class="text-sm font-black">{!! $statusIcon !!}
                                            {{ $item->total_progres }}%</span>
                                        <span class="text-[9px] uppercase font-bold opacity-80 group-hover:opacity-100">
                                            {{ $item->status_pembangunan }}
                                        </span>
                                    </a>
                                </td>

                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->pengawas->nama_lengkap ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap text-center">
                                    @php
                                        $st = $item->status_serah_terima;

                                        $config = [
                                            'pending' => [
                                                'bg' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                                'label' => 'Pending',
                                            ],
                                            'siap_serah_terima' => [
                                                'bg' =>
                                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                'label' => 'Siap Serah Terima',
                                            ],
                                            'siap_lpa' => [
                                                'bg' =>
                                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                'label' => 'Siap LPA',
                                            ],
                                        ];

                                        $current = $config[$st] ?? $config['pending'];
                                    @endphp

                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tight {{ $current['bg'] }} border border-transparent">
                                        {{ $current['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->


    {{-- @include('Etalase.kualifikasi-blok.modal.modal-create-Blok') --}}

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Apakah anda yakin menghapus pembangunan unit ini? Semua data yang terkait dengan pembangunan unit akan ikut terhapus.",
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


    <script>
        if (document.getElementById("table-pembangunan-unit") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-pembangunan-unit", {
                searchable: true,
                sortable: false
            });
        }
    </script>
@endsection
