<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotaBarangMasukDetail;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;

class UpdateHargaSatuanBase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-harga-satuan-base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update harga_satuan_base pada tabel nota_barang_masuk_detail dan update harga_satuan pada stock_ledgers untuk transaksi terdahulu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses update harga_satuan_base...');

        DB::beginTransaction();

        try {
            $details = NotaBarangMasukDetail::with('nota')->get();
            $updatedCount = 0;

            foreach ($details as $detail) {
                // Ambil rasio konversi untuk barang_id dan satuan_id ini
                $konversi = DB::table('barang_satuan_konversi')
                    ->where('barang_id', $detail->barang_id)
                    ->where('satuan_id', $detail->satuan_id)
                    ->value('konversi_ke_base');

                if (!$konversi) {
                    $konversi = 1; // Default
                }

                // Hitung base price
                $hargaSatuanBase = $detail->harga_satuan / $konversi;

                // Update detail
                $detail->update([
                    'harga_satuan_base' => $hargaSatuanBase
                ]);

                // Update StockLedger jika nota sudah diposting
                if ($detail->nota && $detail->nota->status === 'posted') {
                    // Update entri ledger terkait nota masuk ini, spesifik ke barang ini
                    StockLedger::where('ref_type', 'NotaBarangMasuk')
                        ->where('ref_id', $detail->nota_id)
                        ->where('barang_id', $detail->barang_id)
                        ->where('tipe', 'Masuk')
                        ->update(['harga_satuan' => $hargaSatuanBase]);
                }

                $updatedCount++;
            }

            DB::commit();
            $this->info("Berhasil mengupdate {$updatedCount} data nota_barang_masuk_detail.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal melakukan update: ' . $e->getMessage());
        }
    }
}
