<?php

namespace App\Models;

use App\Models\Ubs;
use App\Models\User;
use App\Models\MasterBarang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferGudangHubUbs extends Model
{
    use HasFactory;

    protected $table = 'transfer_gudang_hub_ubs';

    protected $fillable = [
        'barang_id',
        'ke_ubs_id',
        'tanggal_transfer',
        'jumlah_kirim',
        'created_by',
    ];

    protected $casts = [
        'tanggal_transfer' => 'date',
        'jumlah_kirim'     => 'decimal:3',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function ubs()
    {
        return $this->belongsTo(Ubs::class, 'ke_ubs_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
