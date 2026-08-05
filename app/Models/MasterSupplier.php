<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSupplier extends Model
{
    protected $table = 'master_supplier';

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'kategori_supplier',
        'status',
        'npwp',
        'telepon',
        'email',
        'alamat',
        'rekening_bank',
        'no_rekening',
    ];

    public function notas()
    {
        return $this->hasMany(NotaBarangMasuk::class, 'supplier_id');
    }
}
