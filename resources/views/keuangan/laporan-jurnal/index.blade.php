@extends('layouts.app')

@section('pageActive', 'LapoaranJurnal')

@section('content')

    {{-- select 2 --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    {{-- datepicker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'LapoaranJurnal' }">
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
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Laporan Jurnal
                        @if($titlePeriode)
                            <span class="font-semibold">
                                {{ $titlePeriode }}
                            </span>
                        @endif
                    </h3>
                </div>



                {{-- filter and export to pdf-excel --}}
                <form method="GET" action="{{ route('keuangan.laporanJurnal.index') }}" class="relative mb-6 p-6 pt-8 bg-white dark:bg-gray-800 rounded-xl border border-gray-200
                                        dark:border-gray-700 shadow-sm">
                    <span class="absolute -top-3 left-5 px-3 py-1 text-xs font-semibold 
                                        uppercase tracking-widest rounded-md
                                        bg-white dark:bg-gray-800 
                                        text-blue-600 dark:text-blue-400">
                        Filter Laporan
                    </span>

                    <div class="flex flex-wrap items-end gap-4">
                        <!-- UBS / Hub -->
                        <div class="flex-1 min-w-[250px]">
                            <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Pilih UBS / Hub
                            </label>

                            <select name="ubs_id" class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                                            dark:bg-gray-700 dark:text-white border-gray-300" required>
                                {{-- Opsi HUB (All) default --}}
                                <option value="all" {{ request('ubs_id', 'all') == 'all' ? 'selected' : '' }}>
                                    HUB (Pusat)
                                </option>
                                @foreach ($ubsData as $ubs)
                                    <option value="{{ $ubs->id }}" {{ request('ubs_id') == $ubs->id ? 'selected' : '' }}>
                                        {{ $ubs->nama_ubs }}
                                    </option>
                                @endforeach
                            </select>

                        </div>

                        <!-- Tanggal Mulai -->
                        <div class="flex-1 min-w-[160px]">
                            <label
                                class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Dari
                                Tanggal</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <input type="text" id="tanggalStart" name="tanggalStart" autocomplete="off"
                                    value="{{ request('tanggalStart') }}" placeholder="Pilih tanggal"
                                    class="flatpickr bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block w-full ps-10 p-2.5 transition-all outline-none">
                            </div>
                        </div>

                        <!-- Tanggal Akhir -->
                        <div class="flex-1 min-w-[160px]">
                            <label
                                class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sampai
                                Tanggal</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <input type="text" id="tanggalEnd" name="tanggalEnd" value="{{ request('tanggalEnd') }}"
                                    autocomplete="off" placeholder="Pilih tanggal"
                                    class="flatpickr bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block w-full ps-10 p-2.5 transition-all outline-none">
                            </div>
                        </div>

                        <!-- Terapkan -->
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all shadow-sm shadow-blue-200 dark:shadow-none">
                                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Terapkan
                            </button>

                            <a href="{{ route('keuangan.laporanJurnal.index') }}"
                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                                Reset
                            </a>
                        </div>

                        <div class="hidden lg:block w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>

                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" type="button"
                                class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                                <svg class="w-4 h-4 me-2 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export
                                <svg class="w-4 h-4 ms-2 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                class="absolute right-0 z-20 mt-2 w-48 origin-top-right bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div class="p-1">
                                    <a href="{{ route('keuangan.laporanJurnal.exportPdf', request()->all()) }}"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors group">
                                        <svg class="w-5 h-5 me-3 text-gray-400 group-hover:text-red-500" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                            <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                        </svg>
                                        Export as PDF
                                    </a>
                                    <a href="{{ route('keuangan.laporanJurnal.exportExcel', request()->all()) }}"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300
                                                                                                            hover:bg-green-50 dark:hover:bg-green-900/20
                                                                                                            hover:text-green-600 dark:hover:text-green-400 rounded-lg transition-colors group">
                                        <svg class="w-5 h-5 me-3 text-gray-400 group-hover:text-green-500"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z"
                                                clip-rule="evenodd" />
                                        </svg>

                                        Export as Excel
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>


                <div class="relative overflow-auto border border-gray-200 dark:border-gray-700 rounded-xl
                                                 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200"
                    style="max-height: 600px;">
                    <table class="w-full text-sm text-left border-collapse">

                        {{-- HEADER --}}
                        <thead class="sticky top-0 z-10
                                                bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Kode Akun</th>
                                <th class="px-4 py-3">Nama Akun</th>
                                <th class="px-4 py-3 text-center">Debit</th>
                                <th class="px-4 py-3 text-center">Kredit</th>
                                <th class="px-4 py-3">Keterangan</th>
                                @if(Auth::user()->roles->first()->name == 'Superadmin')
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>

                        {{-- BODY --}}
                        <tbody class="bg-white dark:bg-gray-900">
                            @php $lastJurnalId = null; @endphp

                            @foreach ($rows as $row)
                                <tr
                                    class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        @if ($row->jurnal_id !== $lastJurnalId)

                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $row->tanggal->format('d-m-Y') }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                @if ($row->jenis_jurnal == 'umum')
                                                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded border border-blue-200 dark:border-blue-800 uppercase">JU</span>
                                                @elseif($row->jenis_jurnal == 'penyesuaian')
                                                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded border border-purple-200 dark:border-purple-800 uppercase">JP</span>
                                                @endif
                                                <div class="text-[11px] font-mono text-gray-500" title="Nomor Jurnal">
                                                    {{ $row->nomor_jurnal }}
                                                </div>
                                            </div>
                                            @if ($isHub)
                                                <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold mt-1">
                                                    / {{ $row->ubs_abbr }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>


                                    <td class="px-4 py-3">
                                        {{ $row->kode_akun }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $row->nama_akun }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-right
                                                                                                                                                                               border-r border-l
                                                                                                                                                                               border-gray-200 dark:border-gray-700">
                                        {{ $row->debit > 0 ? 'Rp ' . number_format($row->debit, 0, ',', '.') : '-' }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-right
                                                                                                                                                                               border-r border-l
                                                                                                                                                                               border-gray-200 dark:border-gray-700">
                                        {{ $row->kredit > 0 ? 'Rp ' . number_format($row->kredit, 0, ',', '.') : '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $row->jurnal_id !== $lastJurnalId ? $row->keterangan : '' }}
                                    </td>

                                    @if(Auth::user()->roles->first()->name == 'Superadmin')
                                        <td class="px-4 py-3">
                                            @if ($row->jurnal_id !== $lastJurnalId)
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('keuangan.transaksiJurnal.edit', $row->jurnal_id) }}"
                                                        class="p-1.5 text-yellow-600 hover:bg-yellow-100 rounded-lg transition-colors dark:hover:bg-yellow-900/30 dark:text-yellow-400">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                    </a>
                                                    <form action="{{ route('keuangan.transaksiJurnal.destroy', $row->jurnal_id) }}"
                                                        method="POST" class="delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="delete-btn p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors dark:hover:bg-red-900/30 dark:text-red-400">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                </tr>

                                @php $lastJurnalId = $row->jurnal_id; @endphp
                            @endforeach
                        </tbody>

                        {{-- FOOTER TOTAL --}}
                        <tfoot
                            class="sticky bottom-0 z-10 bg-gray-100 dark:bg-gray-800 font-semibold border-t-2 dark:border-gray-600">
                            <tr class="font-semibold">

                                <td colspan="3" class="px-4 py-3 text-right">
                                    TOTAL
                                </td>

                                <td class="px-4 py-3 text-right
                                                        border-r border-l
                                                        border-gray-300 dark:border-gray-600">
                                    <div class="flex justify-between items-center gap-2">
                                        <span>Rp</span>
                                        <span>{{ number_format($totalDebit, 0, ',', '.') }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right
                                                        border-r border-l
                                                        border-gray-300 dark:border-gray-600">
                                    <div class="flex justify-between items-center gap-2">
                                        <span>Rp</span>
                                        <span>{{ number_format($totalKredit, 0, ',', '.') }}</span>
                                    </div>
                                </td>

                                <td colspan="2" class="px-4 py-3 text-right">
                                    @if ($totalDebit == $totalKredit)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor"
                                                viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3" />
                                            </svg>
                                            Balanced
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor"
                                                viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3" />
                                            </svg>
                                            Unbalanced
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>

                    </table>
                </div>


            </div>
        </div>


    </div>
    <!-- ===== Main Content End ===== -->

    <script>
        // Datepicker Flatpickr tanggal jurnal
        document.addEventListener('DOMContentLoaded', function () {
            const startPicker = flatpickr("#tanggalStart", {
                dateFormat: "d-m-Y",
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    endPicker.set('minDate', dateStr);
                }
            });

            const endPicker = flatpickr("#tanggalEnd", {
                dateFormat: "d-m-Y",
                allowInput: true,
                minDate: document.getElementById('tanggalStart').value,
                onChange: function(selectedDates, dateStr) {
                    startPicker.set('maxDate', dateStr);
                }
            });
        });

        // Delete button alert
        document.addEventListener('click', function (e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus transaksi jurnal ini?',
                    text: "Apakah anda yakin menghapus Transaksi Jurnal ini?",
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