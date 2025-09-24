@extends('layouts.app')

@section('pageActive', 'blokLayout')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'blokLayout' }">
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
                        List Blok
                    </h3>


                    <a href="{{ route('blok.create') }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        + Tambah Blok
                    </a>
                </div>

                {{-- filter --}}
                <form method="GET" action="{{ route('blok.index') }}" class="mb-4 flex items-center gap-3"
                    x-data="{
                        tahap: [],
                        async fetchTahap(perumahaanSlug, selectedTahap = null) {
                            if (!perumahaanSlug) { this.tahap = []; return }
                            const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                            if (!res.ok) { console.error('Gagal fetch tahap'); return }
                            this.tahap = await res.json();
                            if (selectedTahap) {
                                this.$nextTick(() => { this.$refs.tahapSelect.value = selectedTahap })
                            }
                        }
                    }" x-init="@if($perumahaanSlug)
                    fetchTahap('{{ $perumahaanSlug }}', '{{ $tahapSlug }}')
                    @endif">
                    <h3 class="text-sm text-gray-500 dark:text-white/90">Filter tag -</h3>

                    <!-- Select Perumahaan -->
                    <div>
                        <select name="perumahaanFil" required
                            @change="fetchTahap($event.target.options[$event.target.selectedIndex].getAttribute('data-slug'))"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                   dark:bg-gray-600 dark:text-white
                   @error('perumahaanFil') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Pilih Perumahaan</option>
                            @foreach ($allPerumahaan as $p)
                                <option value="{{ $p->slug }}" data-slug="{{ $p->slug }}"
                                    {{ $perumahaanSlug === $p->slug ? 'selected' : '' }}>
                                    {{ $p->nama_perumahaan }}
                                </option>
                            @endforeach
                        </select>
                        @error('perumahaanFil')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Select Tahap -->
                    <div>
                        <select x-ref="tahapSelect" name="tahapFil"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                   dark:bg-gray-600 dark:text-white
                   @error('tahapFil') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Pilih Tahap</option>
                            <template x-for="t in tahap" :key="t.id">
                                <option :value="t.slug" x-text="t.nama_tahap"></option>
                            </template>
                        </select>
                        @error('tahapFil')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Terapkan
                    </button>

                    <!-- Tombol Reset -->
                    <a href="{{ route('blok.index') }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                         dark:bg-gray-500 dark:text-white dark:hover:bg-gray-600">
                        Reset
                    </a>
                </form>





                <table id="table-Blok">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Perumahaan
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Tahap
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Nama Blok
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Total Unit
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allBlok as $item)
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <span
                                        class="px-3 py-1 rounded-full text-sm font-medium
                                        {{ $item->perumahaan->nama_perumahaan == 'Asa Dreamland'
                                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-200'
                                            : ($item->perumahaan->nama_perumahaan == 'Lembah Hijau Residence'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}">
                                        {{ $item->perumahaan->nama_perumahaan ?? '-' }}
                                    </span>
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->tahap->nama_tahap }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->nama_blok }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    <a href="" class="text-blue-600 hover:underline">
                                        {{ $item->unit->count() }} Unit
                                    </a>
                                </td>
                                <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                    <a href="{{ route('blok.edit', $item) }}"
                                        class="btn-edit inline-flex items-center gap-1
                                    text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                                    dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                                    px-2.5 py-1.5 rounded-md transition-colors duration-200
                                    focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                                    active:scale-95">
                                        Edit
                                    </a>

                                    <form action="{{ route('blok.destroy', $item) }}" method="POST" class="delete-form">
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


    {{-- modal create kualifikasi-blok --}}
    {{-- @include('Etalase.kualifikasi-blok.modal.modal-create-Blok') --}}

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
    </script>


    <script>
        if (document.getElementById("table-Blok") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-Blok", {
                searchable: true,
                sortable: false
            });
        }
    </script>
@endsection
