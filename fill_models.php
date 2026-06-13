<?php

$contents = [
    'PembangunanProyek.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyek extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_proyek';
    protected \$guarded = [];

    public function pengawas()
    {
        return \$this->belongsTo(User::class, 'pengawas_unit');
    }

    public function orders()
    {
        return \$this->hasMany(PembangunanProyekBarangOrder::class, 'pembangunan_proyek_id');
    }

    public function upah()
    {
        return \$this->hasMany(PembangunanProyekUpah::class, 'pembangunan_proyek_id');
    }
    
    public function pengajuanUpah()
    {
        return \$this->hasMany(PembangunanProyekUpahPengajuan::class, 'pembangunan_proyek_id');
    }
}
PHP,

    'PembangunanProyekBarangOrder.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangOrder extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_proyek_barang_order';
    protected \$guarded = [];

    public function proyek()
    {
        return \$this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }

    public function pembuat()
    {
        return \$this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return \$this->hasMany(PembangunanProyekBarangOrderDetail::class, 'order_id');
    }
    
    public function returns()
    {
        return \$this->hasMany(PembangunanProyekBarangReturn::class, 'order_id');
    }
}
PHP,

    'PembangunanProyekBarangOrderDetail.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangOrderDetail extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_proyek_barang_order_detail';
    protected \$guarded = [];

    public function order()
    {
        return \$this->belongsTo(PembangunanProyekBarangOrder::class, 'order_id');
    }

    public function barang()
    {
        return \$this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuanModel()
    {
        return \$this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
PHP,

    'PembangunanProyekBarangReturn.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangReturn extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_proyek_barang_returns';
    protected \$guarded = [];

    public function proyek()
    {
        return \$this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }

    public function order()
    {
        return \$this->belongsTo(PembangunanProyekBarangOrder::class, 'order_id');
    }

    public function pembuat()
    {
        return \$this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return \$this->hasMany(PembangunanProyekBarangReturnDetail::class, 'return_id');
    }
}
PHP,

    'PembangunanProyekBarangReturnDetail.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangReturnDetail extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_proyek_barang_return_details';
    protected \$guarded = [];

    public function return()
    {
        return \$this->belongsTo(PembangunanProyekBarangReturn::class, 'return_id');
    }

    public function orderDetail()
    {
        return \$this->belongsTo(PembangunanProyekBarangOrderDetail::class, 'order_detail_id');
    }
}
PHP,

    'PembangunanProyekUpah.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekUpah extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_proyek_upah';
    protected \$guarded = [];

    public function proyek()
    {
        return \$this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }
}
PHP,

    'PembangunanProyekUpahPengajuan.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekUpahPengajuan extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_proyek_upah_pengajuan';
    protected \$guarded = [];

    public function proyek()
    {
        return \$this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }
}
PHP,

    'PembangunanKawasan.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasan extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_kawasan';
    protected \$guarded = [];

    public function perumahan()
    {
        return \$this->belongsTo(Perumahaan::class, 'perumahaan_id');
    }

    public function pengawas()
    {
        return \$this->belongsTo(User::class, 'pengawas_kawasan');
    }

    public function orders()
    {
        return \$this->hasMany(PembangunanKawasanBarangOrder::class, 'pembangunan_kawasan_id');
    }

    public function upah()
    {
        return \$this->hasMany(PembangunanKawasanUpah::class, 'pembangunan_kawasan_id');
    }
    
    public function pengajuanUpah()
    {
        return \$this->hasMany(PembangunanKawasanUpahPengajuan::class, 'pembangunan_kawasan_id');
    }
}
PHP,

    'PembangunanKawasanBarangOrder.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangOrder extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_kawasan_barang_order';
    protected \$guarded = [];

    public function kawasan()
    {
        return \$this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }

    public function pembuat()
    {
        return \$this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return \$this->hasMany(PembangunanKawasanBarangOrderDetail::class, 'order_id');
    }
    
    public function returns()
    {
        return \$this->hasMany(PembangunanKawasanBarangReturn::class, 'order_id');
    }
}
PHP,

    'PembangunanKawasanBarangOrderDetail.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangOrderDetail extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_kawasan_barang_order_detail';
    protected \$guarded = [];

    public function order()
    {
        return \$this->belongsTo(PembangunanKawasanBarangOrder::class, 'order_id');
    }

    public function barang()
    {
        return \$this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuanModel()
    {
        return \$this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
PHP,

    'PembangunanKawasanBarangReturn.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangReturn extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_kawasan_barang_returns';
    protected \$guarded = [];

    public function kawasan()
    {
        return \$this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }

    public function order()
    {
        return \$this->belongsTo(PembangunanKawasanBarangOrder::class, 'order_id');
    }

    public function pembuat()
    {
        return \$this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return \$this->hasMany(PembangunanKawasanBarangReturnDetail::class, 'return_id');
    }
}
PHP,

    'PembangunanKawasanBarangReturnDetail.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangReturnDetail extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_kawasan_barang_return_details';
    protected \$guarded = [];

    public function return()
    {
        return \$this->belongsTo(PembangunanKawasanBarangReturn::class, 'return_id');
    }

    public function orderDetail()
    {
        return \$this->belongsTo(PembangunanKawasanBarangOrderDetail::class, 'order_detail_id');
    }
}
PHP,

    'PembangunanKawasanUpah.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanUpah extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_kawasan_upah';
    protected \$guarded = [];

    public function kawasan()
    {
        return \$this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }
}
PHP,

    'PembangunanKawasanUpahPengajuan.php' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanUpahPengajuan extends Model
{
    use HasFactory;

    protected \$table = 'pembangunan_kawasan_upah_pengajuan';
    protected \$guarded = [];

    public function kawasan()
    {
        return \$this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }
}
PHP,

];

$dir = __DIR__ . '/app/Models/';

foreach ($contents as $file => $content) {
    file_put_contents($dir . $file, $content);
    echo "Updated $file\n";
}
