@extends('layouts.app')

@section('pageActive', 'Jurnal')

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
    <div x-data="{ pageName: 'Jurnal' }">
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

    <form action="{{ route('keuangan.transaksiJurnal.store') }}" method="POST">
        @csrf

        {{-- Jurnal Atas --}}
        <div x-data="{ open: true }"
            class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden bg-white dark:bg-gray-900 shadow-sm">

            {{-- Header / Trigger Accordion --}}
            <button type="button" @click="open = !open"
                class="flex items-center justify-between w-full px-5 py-4 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Jurnal
                    </h3>
                </div>
                {{-- Icon Panah --}}
                <svg class="w-5 h-5 text-gray-500 transition-transform duration-300" :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            {{-- Body Accordion --}}
            <div x-show="open" x-collapse x-cloak
                class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Periode --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Periode</label>
                        <select name="periode_id"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white @error('periode_id') border-red-500 @enderror">
                            <option value="">Pilih Periode</option>
                            @foreach ($periodeKeuangan as $periode)
                            <option value="{{ $periode->id }}"
                                {{ old('periode_id', optional($periodeAktif)->id) == $periode->id ? 'selected' : '' }}>
                                {{ $periode->nama_periode }}
                                ({{ \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d M Y') }} –
                                {{ \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d M Y') }})
                            </option>
                            @endforeach
                        </select>
                        @error('periode_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Jurnal --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Tanggal</label>
                        <div class="relative cursor-pointer">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" id="tanggal_jurnal" name="tanggal_jurnal"
                                value="{{ old('tanggal_jurnal', now()->format('Y-m-d')) }}"
                                class="flatpickr bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-pointer @error('tanggal_jurnal') border-red-500 @enderror">
                        </div>
                    </div>

                    {{-- Nomor Jurnal --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Nomor Jurnal</label>
                        <input type="text" name="nomor_jurnal" value="{{ old('nomor_jurnal', $defaultNomorJurnal) }}"
                            class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed opacity-80 focus:bg-white focus:text-gray-900 dark:bg-gray-800 dark:border-gray-600 @error('nomor_jurnal') border-red-500 @enderror" />
                        @error('nomor_jurnal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Default Jenis Jurnal -->
                    <div>
                        <label for="jenis_jurnal" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Jenis Jurnal
                        </label>
                        <select name="jenis_jurnal"
                            class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5
                                cursor-not-allowed
                                dark:bg-gray-800 dark:text-gray-400">
                            <option value="umum" selected>Jurnal Umum</option>
                            <option value="saldo_awal">Saldo Awal</option>
                            <!-- <option value="penyesuaian">Jurnal Penyesuaian</option>
                                <option value="penutup">Jurnal Penutup</option> -->
                        </select>

                        @error('jenis_jurnal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="md:col-span-2">
                        <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                            Keterangan
                        </label>
                        <textarea id="keterangan" name="keterangan" required rows="3" placeholder="Masukkan keterangan jurnal di sini..."
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                resize-none transition-all duration-200
                                focus:ring-1 focus:ring-blue-500 focus:border-blue-500 focus:bg-white
                                dark:bg-gray-700 dark:border-gray-600 dark:text-white
                                @error('keterangan') border-red-500 @enderror">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- jurnal detail transaksi --}}
        <div x-data="jurnalBaris()">
            <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 mt-4 overflow-hidden">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div
                        class="flex justify-between items-center mb-4 border-b-2 border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Transaksi Jurnal
                        </h3>
                        <button type="button" @click="addBaris()"
                            class="px-3 py-1.5 font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                            + Tambah Baris
                        </button>
                    </div>

                    <div class="relative overflow-auto border border-gray-200 dark:border-gray-700 rounded-lg"
                        style="max-height: 400px;">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead
                                class="sticky top-0 z-20 text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 border-b border-r dark:border-gray-700 w-1/2">Kode Akun - Akun
                                    </th>
                                    <th class="px-4 py-3 border-b border-r dark:border-gray-700">Debit</th>
                                    <th class="px-4 py-3 border-b border-r dark:border-gray-700">Kredit</th>
                                    <th class="px-4 py-3 border-b text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-transparent">
                                <template x-for="(baris, index) in barisJurnal" :key="index">
                                    <tr class="group hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                        <td class="p-0 border-b border-r dark:border-gray-700">
                                            <div class="p-2">
                                                <select :name="`items[${index}][akun_id]`" class="select2-akun w-full"
                                                    :data-index="index" required>
                                                    <option value="">Pilih Akun...</option>
                                                    @foreach ($akunKeuangan as $kategori => $daftarAkun)
                                                    <optgroup label="{{ $kategori }}">
                                                        @foreach ($daftarAkun as $akun)
                                                        <option value="{{ $akun->id }}">
                                                            {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                                                        </option>
                                                        @endforeach
                                                    </optgroup>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td class="p-0 border-b border-r dark:border-gray-700">
                                            <input type="text" x-model="baris.display_debit"
                                                @input="updateMask($event, index, 'debit')"
                                                class="w-full h-full border-0 focus:ring-2 focus:ring-inset focus:ring-blue-500 bg-transparent text-gray-900 text-sm p-3 dark:text-white"
                                                placeholder="0">
                                            <input type="hidden" :name="`items[${index}][debit]`"
                                                :value="baris.debit">
                                        </td>

                                        <td class="p-0 border-b border-r dark:border-gray-700">
                                            <input type="text" x-model="baris.display_kredit"
                                                @input="updateMask($event, index, 'kredit')"
                                                class="w-full h-full border-0 focus:ring-2 focus:ring-inset focus:ring-blue-500 bg-transparent text-gray-900 text-sm p-3 dark:text-white"
                                                placeholder="0">
                                            <input type="hidden" :name="`items[${index}][kredit]`"
                                                :value="baris.kredit">
                                        </td>
                                        <td class="p-0 border-b text-center">
                                            <button type="button" @click="removeBaris(index)"
                                                class="p-2 text-red-500 hover:text-red-700 transition-colors"
                                                x-show="barisJurnal.length > 2">
                                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot
                                class="sticky bottom-0 z-20 bg-gray-100 dark:bg-gray-800 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
                                <tr class="text-gray-900 dark:text-white font-bold italic">
                                    <td
                                        class="px-4 py-4 border-r dark:border-gray-700 text-right uppercase tracking-wider text-xs">
                                        Total Transaksi</td>
                                    <td class="px-4 py-4 border-r dark:border-gray-700 text-blue-600 dark:text-blue-400 text-base"
                                        x-text="formatRupiah(totalDebit)"></td>
                                    <td class="px-4 py-4 border-r dark:border-gray-700 text-blue-600 dark:text-blue-400 text-base"
                                        x-text="formatRupiah(totalKredit)"></td>
                                    <td class="bg-gray-100 dark:bg-gray-800"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Status Balance --}}
                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl border-2 transition-all duration-300"
                        :class="totalDebit === totalKredit && totalDebit > 0 ?
                                'bg-green-50 border-green-200 text-green-700' :
                                'bg-amber-50 border-amber-200 text-amber-700'">

                        <div class="flex items-center gap-2 font-semibold">
                            <template x-if="totalDebit === totalKredit && totalDebit > 0">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </template>
                            <span
                                x-text="totalDebit === totalKredit ? 'Status: Seimbang (Balanced)' : 'Status: Belum Seimbang (Unbalanced)'"></span>
                        </div>

                        <div class="text-sm">
                            Selisih:
                            <span class="font-bold underline"
                                x-text="totalDebit === totalKredit ? '0' : formatRupiah(Math.abs(totalDebit - totalKredit))">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @can('keuangan.transaksi-jurnal.create')
        {{-- Tombol --}}
        <div class="flex justify-end gap-3 mt-6">
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                Simpan
            </button>
        </div>
        @endcan
    </form>


</div>
<!-- ===== Main Content End ===== -->


<script>
    // Datepicker Flatpickr tanggal jurnal
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#tanggal_jurnal", {
            dateFormat: "d-m-Y",
            defaultDate: "{{ old('tanggal_jurnal', now()->format('d-m-Y')) }}",
            allowInput: true
        });
    });

    function jurnalBaris() {
        return {
            barisJurnal: [{
                    akun_id: '',
                    debit: 0,
                    kredit: 0,
                    display_debit: '',
                    display_kredit: ''
                },
                {
                    akun_id: '',
                    debit: 0,
                    kredit: 0,
                    display_debit: '',
                    display_kredit: ''
                }
            ],

            // Fungsi Masking Rupiah
            updateMask(e, index, type) {
                let val = e.target.value.replace(/\D/g, ''); // Ambil hanya angka
                this.barisJurnal[index][type] = val || 0; // Simpan angka murni ke value asli

                // Update tampilan dengan format titik (memanggil fungsi global formatRupiah kamu)
                if (type === 'debit') {
                    this.barisJurnal[index].display_debit = val ? formatRupiah(val) : '';
                } else {
                    this.barisJurnal[index].display_kredit = val ? formatRupiah(val) : '';
                }
            },

            addBaris() {
                this.barisJurnal.push({
                    akun_id: '',
                    debit: 0,
                    kredit: 0,
                    display_debit: '',
                    display_kredit: ''
                });
                this.$nextTick(() => {
                    this.initSelect2();
                });
            },

            removeBaris(index) {
                if (this.barisJurnal.length > 1) {
                    this.barisJurnal.splice(index, 1);
                }
            },

            get totalDebit() {
                return this.barisJurnal.reduce((sum, item) => sum + (Number(item.debit) || 0), 0);
            },

            get totalKredit() {
                return this.barisJurnal.reduce((sum, item) => sum + (Number(item.kredit) || 0), 0);
            },

            // Helper untuk tampilan total di footer (IDR Rupiah lengkap)
            formatDisplay(val) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(val);
            },

            initSelect2() {
                $('.select2-akun').select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    placeholder: 'Cari Akun...'
                }).on('change', (e) => {
                    let index = e.target.getAttribute('data-index');
                    this.barisJurnal[index].akun_id = e.target.value;
                });
            },

            init() {
                this.$nextTick(() => {
                    this.initSelect2();
                });
            }
        }
    }
</script>
@endsection