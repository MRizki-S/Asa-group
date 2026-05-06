<?php

namespace App\Console\Commands;

use App\Models\CustomerBooking;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireCustomerBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-customer-booking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat status booking customer menjadi expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $bookings = CustomerBooking::where('status', 'active')
                ->whereDate('tanggal_expired', '<=', now())
                ->lockForUpdate()
                ->get();

            foreach ($bookings as $booking) {

                // Ambil info tambahan
                $customer = $booking->user->nama_lengkap ?? 'Unknown';
                $unitId   = $booking->unit_id;
                $source   = $booking->source;

                $sales    = $booking->sales?->nama_lengkap ?? null;
                $agent    = $booking->agent?->nama ?? null;

                // 1. Update booking → expired
                $booking->update([
                    'status' => 'expired'
                ]);

                // 2. Update unit → available
                Unit::where('id', $unitId)
                    ->where('status_unit', 'booked')
                    ->update([
                        'status_unit' => 'available'
                    ]);

                // 3. Logging
                \Log::info('Booking expired', [
                    'booking_id' => $booking->id,
                    'customer'   => $customer,
                    'unit_id'    => $unitId,
                    'source'     => $source,
                    'sales'      => $sales,
                    'agent'      => $agent,
                    'expired_at' => now()->toDateTimeString(),
                ]);
            }

            DB::commit();

            $this->info('Booking expired berhasil diproses: ' . $bookings->count());
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Cron expire booking gagal', [
                'message' => $e->getMessage(),
            ]);

            $this->error('Error: ' . $e->getMessage());
        }
    }
}
