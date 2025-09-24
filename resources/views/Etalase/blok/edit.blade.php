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

        <form action="{{ route('blok.update', $blok) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Blok --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                        Blok
                    </h3>

                    <div x-data="{
                        tahap: [],
                        async fetchTahap(perumahaanSlug, selectedTahap = null) {
                            if (!perumahaanSlug) { this.tahap = []; return }
                            const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                            if (!res.ok) { console.error('Gagal fetch tahap:', res.status); return; }
                            this.tahap = await res.json();
                            if (selectedTahap) {
                                this.$nextTick(() => {
                                    const el = this.$refs.tahapSelect;
                                    if (el) el.value = selectedTahap;
                                });
                            }
                        }
                    }" x-init="// jika ada old value gunakan itu, jika tidak pakai nilai dari $blok
                    fetchTahap(
                        '{{ optional($allPerumahaan->firstWhere('id', old('perumahaan_id', $blok->perumahaan_id)))->slug }}',
                        '{{ old('tahap_id', $blok->tahap_id) }}'
                    )" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Select Perumahaan -->
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Perumahaan</label>
                            <select name="perumahaan_id" required
                                @change="fetchTahap($event.target.options[$event.target.selectedIndex].getAttribute('data-slug'))"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                        dark:bg-gray-600 dark:text-white
                        @error('perumahaan_id') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Perumahaan</option>
                                @foreach ($allPerumahaan as $p)
                                    <option value="{{ $p->id }}" data-slug="{{ $p->slug }}"
                                        {{ old('perumahaan_id', $blok->perumahaan_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_perumahaan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('perumahaan_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Select Tahap -->
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Tahap</label>
                            <select x-ref="tahapSelect" name="tahap_id" required
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                        dark:bg-gray-600 dark:text-white
                        @error('tahap_id') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Tahap</option>
                                <template x-for="t in tahap" :key="t.id">
                                    <option :value="t.id" x-text="t.nama_tahap"></option>
                                </template>
                            </select>
                            @error('tahap_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Input Nama Blok -->
                        <div class="md:col-span-2">
                            <label for="nama_blok" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nama Blok
                            </label>
                            <input type="text" id="nama_blok" name="nama_blok" required placeholder="Contoh: A, B, C1"
                                value="{{ old('nama_blok', $blok->nama_blok) }}"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                        dark:bg-gray-700 dark:text-white
                        @error('nama_blok') border-red-500 @else border-gray-300 @enderror" />
                            @error('nama_blok')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mb-4 px-5">
                    <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-14 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Simpan
                    </button>
                </div>
            </div>
        </form>



    </div>
    <!-- ===== Main Content End ===== -->
@endsection
