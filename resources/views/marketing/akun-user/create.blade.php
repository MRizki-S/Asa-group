@extends('layouts.app')

@section('pageActive', 'AkunUser')

@section('content')
    {{-- select 2  --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

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

        <form action="{{ route('marketing.akunUser.store') }}" method="POST">
            @csrf

            {{-- Sub 1: Akun User --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                        Akun User
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="nama_lengkap" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" required
                                value="{{ old('nama_lengkap') }}" placeholder="Input Nama Lengkap"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                               dark:bg-gray-700 dark:text-white
                               @error('nama_lengkap') border-red-500 @else border-gray-300 @enderror">
                            @error('nama_lengkap')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- No WhatsApp -->
                        <div>
                            <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                No WhatsApp <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="no_hp" name="no_hp" required value="{{ old('no_hp') }}"
                                placeholder="Contoh: 628123456789"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('no_hp') border-red-500 @else border-gray-300 @enderror">
                            <p class="text-xs text-gray-500 mt-1">Gunakan awalan <span
                                    class="font-medium text-blue-600">62</span> untuk mengganti angka 0</p>

                            @error('no_hp')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- Username -->
                        <div>
                            <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Username <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="text" id="username" name="username" required value="{{ old('username') }}"
                                    placeholder="Input username unik"
                                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 pr-10
                   focus:ring-blue-500 focus:border-blue-500
                   dark:bg-gray-700 dark:text-white
                   @error('username') border-red-500 @else border-gray-300 @enderror">

                                <!-- Icon unik di kanan input -->
                                <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400 dark:text-gray-300"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4v1m0 14v1m8-8h1M4 12H3m15.364-6.364l.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707" />
                                </svg>
                            </div>

                            <!-- Pesan kecil tentang keunikan -->
                            <div
                                class="mt-2 flex items-start gap-2 text-sm text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mt-0.5 flex-shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-9 1V7h2v4H9zm0 2h2v2H9v-2z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p>
                                    Username harus <strong>unik</strong>.
                                    Disarankan menambahkan angka atau huruf acak, misalnya dari
                                    <strong>angka belakang nomor HP</strong> atau kombinasi kecil seperti
                                    <code>-24x</code> agar tidak sama dengan pengguna lain.
                                </p>
                            </div>

                            @error('username')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>



                        <!-- Password -->
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div x-data="{ showPassword: false }" class="relative">
                                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                                    placeholder="Input password"
                                    class="h-11 w-full rounded-lg border bg-gray-50 text-gray-900 text-sm py-2.5 pl-4 pr-11
                   dark:bg-gray-700 dark:text-white
                   @error('password') border-red-500 @else border-gray-300 @enderror" />

                                <!-- Toggle Mata -->
                                <span @click="showPassword = !showPassword"
                                    class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2">
                                    <svg x-show="!showPassword" class="fill-current" width="20" height="20"
                                        viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z"
                                            fill="#98A2B3" />
                                    </svg>
                                    <svg x-show="showPassword" class="fill-current" width="20" height="20"
                                        viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z"
                                            fill="#98A2B3" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Alert error kalau validasi gagal -->
                            @error('password')
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
                            if (!perumahaanSlug) { this.tahap = []; return }
                            const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                            if (res.ok) this.tahap = await res.json();
                        },
                        async fetchUnit(tahapId) {
                            if (!tahapId) { this.unit = []; return }
                            const res = await fetch(`/etalase/tahap/${tahapId}/unit-json`);
                            if (res.ok) this.unit = await res.json();
                        }
                    }" class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <!-- Select Perumahaan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Perumahaan</label>
                            <select name="perumahaan_id" required
                                @change="fetchTahap($event.target.options[$event.target.selectedIndex].getAttribute('data-slug'))"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                       dark:bg-gray-700 dark:text-white
                                       @error('perumahaan_id') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Perumahaan</option>
                                @foreach ($allPerumahaan as $p)
                                    <option value="{{ $p->id }}" data-slug="{{ $p->slug }}"
                                        {{ old('perumahaan_id') == $p->id ? 'selected' : '' }}>
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
                                    <option :value="t.id" x-text="t.nama_tahap"></option>
                                </template>
                            </select>
                            @error('tahap_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Select Unit -->
                        <div x-data x-init="$watch('unit', () => {
                            // Re-initialize Select2 setelah unit di-update
                            $nextTick(() => {
                                const el = document.querySelector('#unitSelect');
                                    if (el) {
                                        $(el).select2({
                                            theme: 'bootstrap4',
                                            placeholder: 'Cari atau pilih unit',
                                            width: '100%',
                                        });
                                    }
                                });
                            });">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit</label>
                            <select id="unitSelect" name="unit_id" required
                                class="select-unit  w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">
                                <option value="">Pilih Unit</option>
                                <template x-for="u in unit" :key="u.id">
                                    <option :value="u.id" x-text="u.nama_unit"></option>
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
        </form>
    </div>
@endsection
