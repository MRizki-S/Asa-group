# Dokumentasi Revisi Alur Order Barang (Produksi & Gudang)

Dokumen ini merangkum secara komprehensif alur baru pengadaan/pengeluaran barang material proyek serta seluruh perubahan arsitektur, database, permission, rute, dan antarmuka (UI) yang telah diterapkan di project ini.

---

## 1. Latar Belakang & Perbandingan Alur

### Alur Lama
1. Penambahan/pengeluaran bahan untuk pembangunan (Unit, Kawasan, Proyek Mangoon) dilakukan langsung oleh bagian Gudang melalui menu **Gudang $\rightarrow$ Permintaan Barang**.
2. Pengawas proyek tidak memiliki antarmuka mandiri untuk memesan barang berdasarkan RAP.
3. Begitu Gudang melakukan ACC, stok langsung terpotong secara FIFO dan data realisasi langsung dicatat dalam satu langkah tanpa penyesuaian/persetujuan berjenjang.

### Alur Baru (3-Step Workflow)
Alur baru mengadopsi pemisahan tugas (*separation of concerns*) dan alur persetujuan berjenjang 3 tahap:

```
[Tahap 1: Pengawas Proyek]
Menu Produksi -> Buat Order (Katalog & Keranjang)
Status: "diproses" (Menunggu Gudang)
          │
          ▼ (Notifikasi WA ke Gudang)
[Tahap 2: Staff Gudang]
Menu Gudang -> Cek Fisik & Input Qty Rilis Riil (jumlah_acc) + Catatan Gudang
Status: "menunggu_spv" (Menunggu ACC SPV)
          │
          ▼ (Notifikasi WA ke SPV Logistik)
[Tahap 3: SPV Logistik]
Menu Gudang -> Review & ACC Resmi
Status: "selesai"
- Stok FIFO dipotong otomatis
- Realisasi data bahan masuk ke termin unit
- Diterbitkan Nomor Nota Barang Keluar (NBK)
- Cetak Nota Barang Keluar (PDF)
          │
          ▼ (Notifikasi WA ke Grup)
```

> **Catatan Cakupan:**
> Sesuai instruksi, revisi ini difokuskan dan diuji secara penuh pada **Pembangunan Unit** terlebih dahulu. Setelah alur unit stabil dan berjalan baik, struktur yang sama dapat direplikasi ke Pembangunan Kawasan dan Proyek Mangoon.

---

## 2. Rincian 3 Langkah Workflow

### Tahap 1: Pengawas Proyek Melakukan Pemesanan (Sisi Produksi)
- **Aktor:** Pengawas Proyek (`PENGAWAS PROYEK (UNIT)` atau role yang memiliki permission produksi).
- **Lokasi Menu:** Sidebar Produksi $\rightarrow$ Properti $\rightarrow$ Pemb. Unit $\rightarrow$ **Order Barang Unit**.
- **Fitur & UI:**
  - UI menggunakan sistem **Katalog Material & Keranjang Belanja (Cart)**.
  - Memilih Pembangunan Unit dan Tahap QC yang bersangkutan.
  - Menampilkan katalog barang gudang/UBS (stok terkini, satuan bertingkat/konversi).
  - Validasi batas RAP dan peringatan jika item di luar RAP atau melebihi RAP.
  - Setelah pesanan disubmit:
    - Status order tercatat sebagai `diproses` (*Menunggu Gudang*).
    - Notifikasi WhatsApp otomatis terkirim ke tim Gudang via Fonnte.

### Tahap 2: Staff Gudang Menyiapkan & Menyesuaikan Kuantitas (Sisi Gudang)
- **Aktor:** Staff Gudang (`gudang.permintaan-barang.pemb-unit.aksi`).
- **Lokasi Menu:** Gudang $\rightarrow$ Permintaan Barang $\rightarrow$ Permintaan Barang Unit $\rightarrow$ Tombol **Detail**.
- **Fitur & UI:**
  - Staff gudang memeriksa ketersediaan fisik stok di gudang.
  - Pada tabel detail barang terdapat kolom **Qty Gudang (Rilis)** (`items_acc[id]`). Jika stok gudang tidak mencukupi atau perlu disesuaikan (misal diminta 10 sak, hanya dikeluarkan 7 sak), staff gudang dapat mengubah nilai kuantitas rilis riil tersebut.
  - **Sistem Non-Backlog:** Jika gudang hanya merilis 7 dari 10 sak yang diminta, maka NBK hanya mencatat 7 sak tersebut. Sisa kekurangan 3 sak tidak menggantung, melainkan dapat diorder ulang secara terpisah bila dibutuhkan.
  - Staff gudang mengklik tombol **"Kirim ke SPV"** dan dapat menambahkan **Catatan Gudang**.
  - **Status berubah menjadi `menunggu_spv`**.
  - Pada tahap ini, **stok fisik belum dipotong dan data real bahan belum masuk ke termin**.
  - Notifikasi WhatsApp otomatis terkirim ke SPV Logistik.

### Tahap 3: SPV Logistik Melakukan Persetujuan Resmi (ACC Final)
- **Aktor:** SPV Logistik (`gudang.permintaan-barang.spv-acc`).
- **Lokasi Menu:** Gudang $\rightarrow$ Permintaan Barang $\rightarrow$ Permintaan Barang Unit $\rightarrow$ Detail.
- **Fitur & UI:**
  - SPV Logistik meninjau rincian barang, jumlah permintaan awal vs kuantitas rilis dari gudang, serta catatan gudang.
  - SPV Logistik mengklik tombol **"ACC SPV Logistik"** melalui modal konfirmasi resmi (atau menolak jika tidak sesuai).
  - Ketika SPV Logistik menyetujui:
    1. Sistem memotong stok gudang UBS secara **FIFO** (`consumeNotaFifo`).
    2. Mencatat mutasi keluar pada `stock_ledger` dan relasi layer FIFO ke `pembangunan_unit_barang_fifo_usages`.
    3. Mencatat/mengakumulasi data realisasi pemakaian bahan ke `pembangunan_unit_bahan` untuk perhitungan termin.
    4. Menerbitkan nomor **Nota Barang Keluar (NBK)** resmi secara otomatis (format: `NBK-YYYYMMDD-XXXX`).
    5. Status order berubah menjadi `selesai`.
    6. Muncul tombol **"Cetak Nota Barang Keluar (PDF)"** untuk mencetak/mengunduh dokumen fisik bertanda tangan 3 pihak (Pengawas, Gudang, SPV).
    7. Notifikasi WhatsApp konfirmasi selesai dikirimkan ke grup terkait.

---

## 3. Perubahan Database & Model

### Migrasi Baru
1. File: `database/migrations/2026_09_20_104500_add_spv_approval_to_pembangunan_unit_barang_order_table.php`
- **Kolom tambahan pada tabel `pembangunan_unit_barang_order`:**
  - `nomor_nbk` (string, nullable) - Nomor unik Nota Barang Keluar resmi.
  - `gudang_by` (foreign key ke `users.id`, nullable) - User staf gudang yang memproses rilis.
  - `tanggal_gudang` (timestamp, nullable) - Waktu staf gudang merilis pesanan.
  - `catatan_gudang` (text, nullable) - Catatan/keterangan dari gudang untuk SPV.
  - `spv_by` (foreign key ke `users.id`, nullable) - User SPV Logistik yang melakukan approval.
  - `tanggal_spv` (timestamp, nullable) - Waktu persetujuan resmi SPV Logistik.
- **Kolom tambahan pada tabel `pembangunan_unit_barang_order_detail`:**
  - `jumlah_acc` (decimal 15,4, nullable) - Kuantitas barang yang disetujui/dirilis riil oleh gudang.
  - `jumlah_acc_base` (decimal 15,4, nullable) - Kuantitas rilis dikonversikan ke satuan dasar (base unit).

2. File: `database/migrations/2026_09_20_123600_add_menunggu_spv_to_status_order_enum.php`
- Menambahkan opsi nilai `'menunggu_spv'` ke kolom `status_order` (ENUM) pada tabel `pembangunan_unit_barang_order` (`ENUM('diproses', 'menunggu_spv', 'selesai', 'ditolak', 'pengembalian')`).

### Perubahan Model
1. **`App\Models\PembangunanUnitBarangOrder`**:
   - Menambahkan field baru ke `$fillable` (`nomor_nbk`, `gudang_by`, `tanggal_gudang`, `catatan_gudang`, `spv_by`, `tanggal_spv`).
   - Menambahkan cast `datetime` untuk `tanggal_gudang` dan `tanggal_spv`.
   - Menambahkan relasi `gudangBy()` dan `spvBy()` ke model `User`.
2. **`App\Models\PembangunanUnitBarangOrderDetail`**:
   - Menambahkan field baru ke `$fillable` (`jumlah_acc`, `jumlah_acc_base`).

---

## 4. Hak Akses (Permissions & Roles)

### Seeder: `MenuGudangSeeder.php`
- `gudang.permintaan-barang.spv-acc`
- `gudang.permintaan-barang.pemb-unit.spv-acc`

### Seeder: `ProduksiPermissionSeeder.php`
- `produksi.properti.pembangunan-unit.order-barang.read`
- `produksi.properti.pembangunan-unit.order-barang.create`
- `produksi.properti.pembangunan-unit.order-barang.delete`

---

## 5. Rute (Routes) Baru di `routes/web.php`

### Sisi Produksi (Pengawas Proyek)
- `GET /produksi/pembangunan-unit-order-barang` $\rightarrow$ `PembangunanUnitOrderBarangController@index` (Nama rute: `produksi.pembangunanUnit.orderIndex`)
- `GET /produksi/pembangunan-unit-order-barang/create` $\rightarrow$ `PembangunanUnitOrderBarangController@create` (Nama rute: `produksi.pembangunanUnit.orderCreate`)
- `POST /produksi/pembangunan-unit/order-barang` $\rightarrow$ `PembangunanUnitOrderBarangController@store` (Nama rute: `produksi.pembangunanUnit.orderStore`)

### Sisi Gudang & SPV Logistik
- `PATCH /gudang/permintaan-barang/pembangunan-unit/{id}/spv-acc` $\rightarrow$ `PermintaanBarangPembangunanUnitController@spvAccBarangOrder` (Nama rute: `gudang.permintaanBarang.pembangunanUnit.spvAcc`)
- `GET /gudang/permintaan-barang/pembangunan-unit/{id}/nota-pdf` $\rightarrow$ `PermintaanBarangPembangunanUnitController@notaPdf` (Nama rute: `gudang.permintaanBarang.pembangunanUnit.notaPdf`)

---

## 6. Controller & Business Logic

1. **`App\Http\Controllers\Produksi\PembangunanUnit\PembangunanUnitOrderBarangController`**:
   - Menangani daftar order pengawas (`index`) dengan filter perumahan & pengawas.
   - Menangani katalog pemilihan bahan dan kalkulasi stok gudang (`create`).
   - Menyimpan order berstatus `diproses` serta memicu notifikasi WA awal (`store`).
2. **`App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangPembangunanUnitController`**:
   - `accBarangOrder()`: Di-refactor sehingga **hanya** menyimpan `jumlah_acc`, `catatan_gudang`, user staf gudang, dan menaikkan status order menjadi `menunggu_spv`. Tidak lagi memotong stok ataupun mengubah realisasi unit.
   - `spvAccBarangOrder()`: Method baru untuk SPV Logistik. Menghasilkan `nomor_nbk`, memicu pemotongan stok FIFO (`processAcc`), mencatat data realisasi unit, mengubah status menjadi `selesai`, dan mengirim WA konfirmasi.
   - `notaPdf()`: Menghasilkan dokumen PDF Nota Barang Keluar menggunakan format standar.
   - `resolveJumlahBase()`: Diperbarui agar memprioritaskan `jumlah_acc` (kuantitas rilis riil) dibanding kuantitas pesanan awal jika `jumlah_acc` telah diisi.
3. **`App\Http\Controllers\Gudang\PermintaanBarang\PermintaanBarangController`**:
   - Opsi status dan filter daftar permintaan diperbarui agar mencakup status `menunggu_spv` (*Menunggu ACC SPV*).

---

## 7. Antarmuka (Views) & Notifikasi

1. **`resources/views/partials/sidebar.blade.php`**:
   - Menu baru **"Order Barang Unit"** pada sidebar Produksi di bawah submenu Pembangunan Unit.
2. **`resources/views/produksi/pembangunan-unit/order-barang/index.blade.php`**:
   - Tabel riwayat order pengawas, filter status, indikator status berwarna, tombol buat order, dan tombol download NBK PDF.
3. **`resources/views/produksi/pembangunan-unit/order-barang/create.blade.php`**:
   - Form pembuatan order lengkap dengan pencarian unit, load otomatis QC checklist RAP, katalog barang gudang, dan keranjang belanja (cart).
4. **`resources/views/gudang/permintaan-barang/show.blade.php`**:
   - Kolom Qty Rilis Gudang pada tabel detail.
   - Log panel workflow yang menampilkan data staf gudang, SPV logistik, catatan gudang, dan nomor NBK.
   - Pemisahan tombol aksi: Kirim ke SPV (Gudang) vs ACC SPV Logistik (SPV) vs Cetak NBK PDF (Saat selesai).
5. **`resources/views/gudang/permintaan-barang/index.blade.php`**:
   - Badge status `menunggu_spv` (*Menunggu ACC SPV*) berwarna amber.
6. **`resources/views/gudang/permintaan-barang/nota-keluar-pdf.blade.php`**:
   - Template cetak Nota Barang Keluar (NBK) berformat PDF dengan tabel barang dan kolom tanda tangan (Pengawas Proyek, Staff Gudang, SPV Logistik).
7. **Template Notifikasi WhatsApp (`resources/views/notifications/whatsapp/pembangunan_unit/`)**:
   - `order_barang.blade.php`: Pengawas membuat order baru $\rightarrow$ notif ke Gudang.
   - `gudang_order_barang.blade.php`: Gudang selesai menyiapkan barang $\rightarrow$ notif ke SPV Logistik.
   - `spv_acc_order_barang.blade.php`: SPV Logistik melakukan ACC resmi $\rightarrow$ notif ke Pengawas & Grup.
