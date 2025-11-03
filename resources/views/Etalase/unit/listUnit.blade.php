@extends('layouts.app')

@section('pageActive', 'unitLayout')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'unitLayout' }">
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
        @endif`

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                {{-- tambah data --}}
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        List Unit - <span
                            class="px-3 py-1 rounded-full text-sm font-medium
                                    {{ $perumahaan->nama_perumahaan == 'Asa Dreamland'
                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-200'
                                        : ($perumahaan->nama_perumahaan == 'Lembah Hijau Residence'
                                            ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}">
                            {{ $perumahaan->nama_perumahaan ?? '-' }}
                        </span>
                    </h3>


                    <a href="{{ route('unit.create', $perumahaan->slug) }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        + Tambah Unit
                    </a>
                </div>

                {{-- Filter Unit --}}
                <form method="GET" action="{{ route('unit.index', $perumahaan->slug) }}"
                   class="mb-4 flex items-center gap-3" x-data="{
                        selectedTahap: '{{ request('tahapFil') ?? '' }}',
                        selectedBlok: '{{ request('blokFil') ?? '' }}',
                        selectedType: '{{ request('typeFil') ?? '' }}',
                        tahap: [],
                        blok: [],
                        type: [],
                        async fetchTahap(perumahaanSlug) {
                            if (!perumahaanSlug) { this.tahap = []; return }
                            const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                            if (!res.ok) { console.error('Gagal fetch tahap'); return }
                            this.tahap = await res.json();
                        },
                        async fetchBlok(perumahaanSlug, tahapSlug) {
                            if (!perumahaanSlug || !tahapSlug) { this.blok = []; return }
                            const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/blok-json?tahap=${tahapSlug}`);
                            if (!res.ok) { console.error('Gagal fetch blok'); return }
                            this.blok = await res.json();
                        },
                        async fetchType(perumahaanSlug, tahapSlug) {
                            if (!perumahaanSlug || !tahapSlug) { this.type = []; return }
                            const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/type-json?tahap=${tahapSlug}`);
                            if (!res.ok) { console.error('Gagal fetch type'); return }
                            this.type = await res.json();
                        },
                        updateBlokType() {
                            this.fetchBlok('{{ $perumahaan->slug }}', this.selectedTahap);
                            this.fetchType('{{ $perumahaan->slug }}', this.selectedTahap);
                        }
                    }" x-init="fetchTahap('{{ $perumahaan->slug }}').then(() => {
                        if (selectedTahap) updateBlokType();
                    })">
                    <h3 class="text-sm text-gray-500 dark:text-white/90">Filter tag -</h3>

                    <!-- Select Tahap -->
                  <div>
                      <select name="tahapFil" x-model="selectedTahap" @change="updateBlokType()"
                        class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white border-gray-300">
                        <option value="">Pilih Tahap</option>
                        <template x-for="t in tahap" :key="t.id">
                            <option :value="t.slug" x-text="t.nama_tahap" :selected="t.slug === selectedTahap">
                            </option>
                        </template>
                    </select>
                  </div>

                    <!-- Select Blok -->
                    {{-- <select name="blokFil" x-model="selectedBlok"
                        class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white border-gray-300">
                        <option value="">Pilih Blok</option>
                        <template x-for="b in blok" :key="b.id">
                            <option :value="b.slug" x-text="b.nama_blok" :selected="b.slug === selectedBlok">
                            </option>
                        </template>
                    </select> --}}

                    <!-- Select Type -->
                    {{-- <select name="typeFil" x-model="selectedType"
                        class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white border-gray-300">
                        <option value="">Pilih Tipe</option>
                        <template x-for="ty in type" :key="ty.id">
                            <option :value="ty.id"
                                x-text="`${ty.nama_type} — Rp${Number(ty.harga_dasar).toLocaleString('id-ID')}`"
                                :selected="ty.id == selectedType"></option>
                        </template>
                    </select> --}}

                   <button type="submit"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Terapkan
                    </button>

                    <!-- Tombol Reset -->
                    <a href="{{ route('unit.index', $perumahaan->slug) }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                         dark:bg-gray-500 dark:text-white dark:hover:bg-gray-600">
                        Reset
                    </a>
                </form>

                <table id="table-unit">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Tahap
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                               <span class="flex items-center">
                                    Blok
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                <span class="flex items-center">
                                    Nama Unit
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                <span class="flex items-center">
                                    Tipe Unit
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Status Unit
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                <span class="flex items-center">
                                    Harga
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($units as $item)
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->tahap->nama_tahap }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->blok->nama_blok }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->nama_unit }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->type->nama_type }}
                                </td>
                                <td class="text-center">
                                    <span @class([
                                        // Base style badge
                                        'inline-block px-3 py-1 rounded-full text-xs font-semibold',
                                        // Conditional warna
                                        'bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-100' =>
                                            $item->status_unit === 'available',
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-600 dark:text-yellow-100' =>
                                            $item->status_unit === 'booked',
                                        'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-100' =>
                                            $item->status_unit === 'sold',
                                    ])>
                                        {{ ucfirst($item->status_unit) }}
                                    </span>
                                </td>

                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    Rp {{ number_format($item->harga_final, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                    <a href="{{ route('unit.show', parameters: ['perumahaan' => $perumahaan->slug, 'unit' => $item]) }}"
                                        class="btn-edit inline-flex items-center gap-1
                                    text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200
                                    dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700
                                    px-2.5 py-1.5 rounded-md transition-colors duration-200
                                    focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1
                                    active:scale-95">
                                        Lihat
                                    </a>
                                    <a href="{{ route('unit.edit', parameters: ['perumahaan' => $perumahaan->slug, 'unit' => $item]) }}"
                                        class="btn-edit inline-flex items-center gap-1
                                    text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                                    dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                                    px-2.5 py-1.5 rounded-md transition-colors duration-200
                                    focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                                    active:scale-95">
                                        Edit
                                    </a>


                                    <form
                                        action="{{ route('unit.destroy', ['perumahaan' => $perumahaan->slug, 'unit' => $item]) }}"
                                        method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="delete-btn px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->

    {{-- sweatalert 2 for delete data --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Apakah anda yakin menghapus kualifikasi blok ini? Semua data yang terkait dengan kualifikasi blok akan ikut terhapus.",
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

        if (document.getElementById("table-unit") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-unit", {
                searchable: true,
                sortable: true,
                perPageSelect: [5, 10, 20, 50],
            });
        }
    </script>
@endsection
