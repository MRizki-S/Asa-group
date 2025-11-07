<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Data Diri User
            </h3>
        </div>

        <div x-data="wilayahForm" x-init="loadProvinsi()" class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Nama Pribadi -->
            <div>
                <label for="nama_pribadi" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama User <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_pribadi" name="nama_pribadi"
                    class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
            dark:bg-gray-700 dark:text-white dark:border-gray-600"
                    placeholder="Masukkan nama lengkap" required>
            </div>

            <!-- Nomor HP -->
            <div>
                <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nomor HP <span class="text-red-500">*</span>
                </label>
                <input type="number" id="no_hp" name="no_hp" required
                    class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
            dark:bg-gray-700 dark:text-white dark:border-gray-600"
                    placeholder="Contoh: 081234567890">
            </div>

            <!-- Nomor KTP -->
            <div>
                <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    No KTP <span class="text-red-500">*</span>
                </label>
                <input type="text" id="no_ktp" name="no_ktp" required
                    class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
            dark:bg-gray-700 dark:text-white dark:border-gray-600"
                    placeholder="No KTP">
            </div>

            <!-- Pekerjaan -->
            <div x-data="{
                pekerjaan: '',
                listPekerjaan: [
                    'PNS / ASN',
                    'Karyawan Swasta',
                    'Wiraswasta',
                    'Pengusaha',
                    'Lainnya'
                ]
            }">
                <label for="pekerjaan"
                    class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <div class="flex items-center gap-1">
                        <span>Pekerjaan</span>
                        <span class="text-red-500">*</span>
                    </div>
                </label>

                <select id="pekerjaan" name="pekerjaan" required x-model="pekerjaan"
                    class="select-blok w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
               dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:placeholder-gray-400">
                    <option value="">Pilih Pekerjaan</option>
                    <template x-for="item in listPekerjaan" :key="item">
                        <option :value="item" x-text="item"></option>
                    </template>
                </select>
            </div>



            <!-- Provinsi -->
            <div>
                <label for="provinsi_code"
                    class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">

                    <span
                        class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-semibold">1</span>
                    <div class="flex items-center gap-1">
                        <span>Provinsi</span>
                        <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500">(isi dulu)</span>
                    </div>
                </label>

                <select id="provinsi_code" name="provinsi_code" required x-model="provinsi_code"
                    :disabled="isLoadingProvinsi"
                    class="select-blok w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
           dark:bg-gray-700 dark:text-white">
                    <template x-if="isLoadingProvinsi">
                        <option value="">🔄 Sedang memuat provinsi...</option>
                    </template>
                    <template x-if="!isLoadingProvinsi">
                        <option value="">Pilih Provinsi</option>
                    </template>
                    <template x-for="item in listProvinsi" :key="item.code">
                        <option :value="item.code" x-text="item.name"></option>
                    </template>
                </select>
                <input type="hidden" name="provinsi_nama" x-model="provinsi_nama">
            </div>

            <!-- Kota -->
            <div>
                <label for="kota_code"
                    class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <span
                        class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-semibold">2</span>
                    <div class="flex items-center gap-1">
                        <span>Kota / Kabupaten</span>
                        <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500">(isi setelah provinsi)</span>
                    </div>
                </label>
                <select id="kota_code" name="kota_code" x-model="kota_code" :disabled="!provinsi_code || isLoadingKota"
                    required
                    class="select-blok w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
           dark:bg-gray-700 dark:text-white">
                    <template x-if="isLoadingKota">
                        <option value="">🔄 Sedang memuat kota...</option>
                    </template>
                    <template x-if="!isLoadingKota">
                        <option value="">Pilih Kota / Kabupaten</option>
                    </template>
                    <template x-for="item in listKota" :key="item.code">
                        <option :value="item.code" x-text="item.name"></option>
                    </template>
                </select>
                <input type="hidden" name="kota_nama" x-model="kota_nama">
            </div>

            <!-- Kecamatan -->
            <div>
                <label for="kecamatan_code"
                    class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <span
                        class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-semibold">3</span>
                    <div class="flex items-center gap-1">
                        <span>Kecamatan</span>
                        <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500">(isi setelah kota / kabupaten)</span>
                    </div>
                </label>
                <select id="kecamatan_code" name="kecamatan_code" x-model="kecamatan_code" required
                    :disabled="!kota_code || isLoadingKecamatan"
                    class="select-blok w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
           dark:bg-gray-700 dark:text-white">
                    <template x-if="isLoadingKecamatan">
                        <option value="">🔄 Sedang memuat kecamatan...</option>
                    </template>
                    <template x-if="!isLoadingKecamatan">
                        <option value="">Pilih Kecamatan</option>
                    </template>
                    <template x-for="item in listKecamatan" :key="item.code">
                        <option :value="item.code" x-text="item.name"></option>
                    </template>
                </select>
                <input type="hidden" name="kecamatan_nama" x-model="kecamatan_nama">
            </div>

            <!-- Desa -->
            <div>
                <label for="desa_code"
                    class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <span
                        class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-semibold">4</span>
                    <div class="flex items-center gap-1">
                        <span>Desa / Kelurahan</span>
                        <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500">(isi setelah kecamatan)</span>
                    </div>
                </label>

                <select id="desa_code" name="desa_code" x-model="desa_code"
                    :disabled="!kecamatan_code || isLoadingDesa" required
                    class="select-blok w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-700 dark:text-white">
                    <template x-if="isLoadingDesa">
                        <option value="">🔄 Sedang memuat data desa...</option>
                    </template>
                    <template x-if="!isLoadingDesa">
                        <option value="">Pilih Desa / Kelurahan</option>
                    </template>
                    <template x-for="item in listDesa" :key="item.code">
                        <option :value="item.code" x-text="item.name"></option>
                    </template>
                </select>

                <input type="hidden" name="desa_nama" x-model="desa_nama">
            </div>

            <!-- RT -->
            <div>
                <label for="rt" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    RT <span class="text-red-500">*</span>
                </label>
                <input type="text" id="rt" name="rt" required placeholder="Contoh: 02"
                    class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
            dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>

            <!-- RW -->
            <div>
                <label for="rw" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    RW <span class="text-red-500">*</span>
                </label>
                <input type="text" id="rw" name="rw" required placeholder="Contoh: 01"
                    class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
            dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>

            <!-- Jalan / Dusun -->
            <div class="md:col-span-2">
                <label for="alamat_detail" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Jalan / Dusun <span class="text-red-500">*</span>
                </label>
                <input type="text" id="alamat_detail" required name="alamat_detail"
                    placeholder="Contoh: Jl. Mawar No. 7, Dusun Sari"
                    class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
            dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
        </div>
    </div>
</div>
