@extends('layouts.app')

@section('pageActive', 'AkunUser')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'AkunUser' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
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

        <form action="{{ route('marketing.akunUser.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Sub 1: Akun User --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                        Akun user
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Username -->
                        <div>
                            <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="username" name="username" required
                                value="{{ old('username', $user->username) }}" placeholder="Input username"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('username') border-red-500 @else border-gray-300 @enderror">
                            @error('username')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Password
                            </label>
                            <div x-data="{ showPassword: false }" class="relative">
                                <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                                    placeholder="Biarkan kosong jika tidak diubah"
                                    class="h-11 w-full rounded-lg border bg-gray-50 text-gray-900 text-sm py-2.5 pl-4 pr-11
                                dark:bg-gray-700 dark:text-white
                                @error('password') border-red-500 @else border-gray-300 @enderror" />

                                <!-- Toggle Mata -->
                                <span @click="showPassword = !showPassword"
                                    class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2">
                                    <svg x-show="!showPassword" class="fill-current" width="20" height="20"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619Z" />
                                    </svg>
                                    <svg x-show="showPassword" class="fill-current" width="20" height="20"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229Z" />
                                    </svg>
                                </span>
                            </div>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- No WhatsApp -->
                        <div>
                            <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                No WhatsApp <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="no_hp" name="no_hp" required
                                value="{{ old('no_hp', $user->no_hp) }}" placeholder="Contoh: 628123456789"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('no_hp') border-red-500 @else border-gray-300 @enderror">
                            <p class="text-xs text-gray-500 mt-1">Gunakan awalan <span
                                    class="font-medium text-blue-600">62</span> untuk mengganti angka 0</p>
                            @error('no_hp')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sub 2: Booking Unit --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                        Booking Unit
                    </h3>

                    <div x-data="{
                        tahap: [],
                        unit: [],
                        async fetchTahap(perumahaanSlug) {
                            // Reset unit setiap kali perumahaan diganti
                            this.unit = [];
                            if (!perumahaanSlug) {
                                this.tahap = [];
                                return;
                            }

                            try {
                                const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                                if (res.ok) {
                                    this.tahap = await res.json();
                                }
                            } catch (error) {
                                console.error('Gagal fetch tahap:', error);
                            }
                        },
                        async fetchUnit(tahapId) {
                            if (!tahapId) {
                                this.unit = [];
                                return;
                            }

                            try {
                                // Ambil ID unit lama (jika mode edit)
                                const currentUnit = '{{ $user->booking->unit_id ?? '' }}';
                                const res = await fetch(`/etalase/tahap/${tahapId}/unit-json?current_unit_id=${currentUnit}`);

                                if (res.ok) {
                                    this.unit = await res.json();
                                }
                            } catch (error) {
                                console.error('Gagal fetch unit:', error);
                            }
                        }
                    }" x-init="// Auto-load data saat edit
                    fetchTahap('{{ $user->booking->perumahaan->slug ?? '' }}');
                    fetchUnit('{{ $user->booking->tahap_id ?? '' }}');" class="grid grid-cols-1 md:grid-cols-3 gap-4">



                        <!-- Select Perumahaan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Perumahaan</label>
                            <select name="perumahaan_id" required
                                @change="fetchTahap($event.target.options[$event.target.selectedIndex].dataset.slug)"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-700 dark:text-white
                                @error('perumahaan_id') border-red-500 @else border-gray-300 @enderror">

                                <option value="">Pilih Perumahaan</option>
                                @foreach ($allPerumahaan as $p)
                                    <option value="{{ $p->id }}" data-slug="{{ $p->slug }}"
                                        {{ old('perumahaan_id', $user->booking->perumahaan_id ?? '') == $p->id ? 'selected' : '' }}>
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
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tahap</label>
                            <select name="tahap_id" required @change="fetchUnit($event.target.value)"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('tahap_id') border-red-500 @else border-gray-300 @enderror">

                                <option value="">Pilih Tahap</option>

                                <template x-for="t in tahap" :key="t.id">
                                    <option :value="t.id" x-text="t.nama_tahap"
                                        :selected="t.id == {{ old('tahap_id', $user->booking->tahap_id ?? 'null') }}">
                                    </option>
                                </template>
                            </select>
                            @error('tahap_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Select Unit -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit</label>
                            <select name="unit_id" required
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-700 dark:text-white
                                @error('unit_id') border-red-500 @else border-gray-300 @enderror">

                                <option value="">Pilih Unit</option>

                                <template x-for="u in unit" :key="u.id">
                                    <option :value="u.id" x-text="u.nama_unit"
                                        :selected="u.id == {{ old('unit_id', $user->booking->unit_id ?? 'null') }}">
                                    </option>
                                </template>
                            </select>
                            @error('unit_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            @can('marketing.customer.update')
            <div class="flex justify-end gap-2">
                <button type="button" onclick="history.back()"
                    class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                    Kembali
                </button>
                <button type="submit"
                    class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Simpan
                </button>
            </div>
            @endcan
        </form>
    </div>
@endsection
