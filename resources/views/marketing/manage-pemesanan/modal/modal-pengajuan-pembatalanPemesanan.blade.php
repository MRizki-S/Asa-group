<!-- ===== Modal Create Pembatalan ===== -->
<div id="modal-pembatalan" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-99999 flex items-center justify-center w-full h-full overflow-y-auto overflow-x-hidden">

    <div class="relative w-full max-w-2xl p-4">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 overflow-hidden">

            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-rose-500 px-5 py-3 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-white">Pengajuan Pembatalan Pemesanan Unit</h3>
                <button type="button" data-modal-hide="modal-pembatalan"
                    class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg w-8 h-8 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <form id="formPembatalan" method="POST" action="{{ route('marketing.pengajuanPembatalan.store') }}"
                enctype="multipart/form-data" class="p-5 space-y-5">
                @csrf

                <input type="hidden" id="pemesanan_id" name="pemesanan_unit_id">

                <!-- Info Unit & User -->
                <div class="grid grid-cols-2 gap-4 text-sm border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div>
                        <p class="text-gray-500 text-xs uppercase font-medium">Nama Unit</p>
                        <p class="text-gray-900 font-semibold mt-1" id="nama_unit"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase font-medium">Nama User</p>
                        <p class="text-gray-900 font-semibold mt-1" id="nama_user"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase font-medium">Cara Bayar</p>
                        <p class="text-gray-900 font-semibold mt-1" id="cara_bayar"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase font-medium">No HP</p>
                        <p class="text-gray-900 font-semibold mt-1" id="no_hp"></p>
                    </div>
                </div>

                <!-- Alasan Pembatalan -->
                <div>
                    <label for="alasan_pembatalan" class="block text-sm font-medium mb-1">Alasan Pembatalan</label>
                    <select name="alasan_pembatalan" id="alasan_pembatalan"
                        class="w-full border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                        <option value="">Pilih Alasan Pembatalan</option>
                        <option value="User meninggal dunia">User meninggal dunia</option>
                        <option value="User pindah domisili / keluar kota">User pindah domisili / keluar kota</option>
                        <option value="User pindah proyek perumahan">User pindah proyek perumahan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>


                <!-- Alasan Detail (Opsional) -->
                <div>
                    <label for="alasan_detail" class="block text-sm font-medium mb-1">
                        Alasan Detail <span class="text-gray-500 text-xs">(Opsional)</span>
                    </label>
                    <textarea id="alasan_detail" name="alasan_detail" rows="4" placeholder="Tuliskan penjelasan lebih detail..."
                        class="w-full border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 resize-none"></textarea>
                </div>

                <!-- Bukti Pembatalan -->
                <div>
                    <label for="bukti_pembatalan" class="block text-sm font-medium text-gray-700 mb-1">
                        Upload Bukti Pembatalan
                    </label>
                    <input type="file" name="bukti_pembatalan" id="bukti_pembatalan" accept=".jpg,.jpeg,.png"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer
               bg-white focus:ring-red-500 focus:border-red-500">
                    <p class="mt-1 text-xs text-gray-500">
                        Hanya gambar (.jpg, .jpeg, .png) — ukuran maksimal 2MB.
                    </p>
                </div>


                <!-- Tombol Aksi -->
                <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                    <button type="button" data-modal-hide="modal-pembatalan"
                        class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-100 text-sm text-gray-700">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm shadow-md transition">
                        Ajukan Pembatalan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalButtons = document.querySelectorAll('[data-modal-toggle="modal-pembatalan"]');
        const form = document.getElementById('formPembatalan');

        modalButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // ambil semua data-* dari tombol
                const id = btn.getAttribute('data-id');
                const namaUnit = btn.getAttribute('data-nama-unit');
                const namaUser = btn.getAttribute('data-nama-user');
                const caraBayar = btn.getAttribute('data-cara-bayar');
                const noHp = btn.getAttribute('data-no-hp');

                // isi field modal
                document.getElementById('pemesanan_id').value = id;
                document.getElementById('nama_unit').textContent = namaUnit;
                document.getElementById('nama_user').textContent = namaUser;
                document.getElementById('cara_bayar').textContent = caraBayar;
                document.getElementById('no_hp').textContent = noHp;
            });
        });
    });
</script>
