<?php

$dir = __DIR__ . '/database/migrations';
$files = glob($dir . '/*.php');

function getFileBySuffix($files, $suffix) {
    foreach ($files as $file) {
        if (str_ends_with($file, $suffix . '.php')) {
            return $file;
        }
    }
    return null;
}

$contents = [
    'create_pembangunan_proyek_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek', function (Blueprint \$table) {
            \$table->id();
            \$table->string('nama');
            \$table->foreignId('pengawas_unit')->nullable()->constrained('users')->cascadeOnDelete();
            \$table->timestamp('tanggal_mulai')->nullable();
            \$table->timestamp('tanggal_selesai')->nullable();
            \$table->enum('status_pembangunan', ['pending', 'proses', 'selesai', 'selesai dengan catatan'])->default('pending');
            \$table->text('catatan')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek');
    }
};
PHP,

    'create_pembangunan_proyek_barang_order_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_barang_order', function (Blueprint \$group) {
            \$group->id();
            \$group->foreignId('pembangunan_proyek_id')->constrained('pembangunan_proyek')->cascadeOnDelete();
            \$group->enum('jenis_order', ['stock', 'direct'])->default('stock');
            \$group->text('catatan')->nullable();
            \$group->dateTime('tanggal_diajukan');
            \$group->enum('status_order', ['diproses', 'selesai', 'ditolak', 'pengembalian'])->default('diproses');
            \$group->dateTime('tanggal_selesai')->nullable();
            \$group->foreignId('created_by')->constrained('users');
            \$group->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_order');
    }
};
PHP,

    'create_pembangunan_proyek_barang_order_detail_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_barang_order_detail', function (Blueprint \$group) {
            \$group->id();
            \$group->foreignId('order_id')->constrained('pembangunan_proyek_barang_order')->onDelete('cascade');
            \$group->foreignId('barang_id')->nullable()->constrained('master_barang')->onDelete('set null');
            \$group->foreignId('satuan_id')->nullable()->constrained('master_satuan')->onDelete('set null');
            \$group->unsignedBigInteger('ubs_id')->nullable();

            \$group->decimal('jumlah_input', 18, 3);
            \$group->string('nama_barang')->nullable();
            \$group->string('satuan')->nullable();
            \$group->decimal('jumlah_base', 18, 3);

            \$group->boolean('konfirmasi')->default(false);

            \$group->decimal('jumlah_return', 18, 3)->default(0);
            \$group->text('keterangan_return')->nullable();

            \$group->decimal('harga_satuan_snapshot', 18, 2)->nullable();
            \$group->decimal('harga_total_snapshot', 18, 2)->nullable();

            \$group->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_order_detail');
    }
};
PHP,

    'create_pembangunan_proyek_barang_returns_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_barang_returns', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('pembangunan_proyek_id')->constrained('pembangunan_proyek')->onDelete('cascade');
            \$table->foreignId('order_id')->constrained('pembangunan_proyek_barang_order')->onDelete('cascade');
            \$table->text('alasan_return')->nullable();
            \$table->enum('status_return', ['pending', 'disetujui', 'ditolak'])->default('pending');
            \$table->foreignId('created_by')->constrained('users');
            \$table->dateTime('tanggal_return');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_returns');
    }
};
PHP,

    'create_pembangunan_proyek_barang_return_details_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_barang_return_details', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('return_id')->constrained('pembangunan_proyek_barang_returns', 'id', 'ppbrd_return_fk')->onDelete('cascade');
            \$table->foreignId('order_detail_id')->constrained('pembangunan_proyek_barang_order_detail', 'id', 'ppbrd_order_detail_fk')->onDelete('cascade');
            \$table->decimal('jumlah_return', 18, 3);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_return_details');
    }
};
PHP,

    'create_pembangunan_proyek_upah_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_upah', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('pembangunan_proyek_id')->constrained('pembangunan_proyek')->onDelete('cascade');
            \$table->string('nama_upah');
            \$table->decimal('total_nominal', 15, 2);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_upah');
    }
};
PHP,

    'create_pembangunan_proyek_upah_pengajuans_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_upah_pengajuan', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('pembangunan_proyek_id')->constrained('pembangunan_proyek', 'id', 'ppup_proyek_fk')->onDelete('cascade');
            \$table->string('nama_upah');
            \$table->decimal('nominal_diajukan', 18, 2);
            \$table->text('catatan_pengawas')->nullable();
            \$table->enum('status_pengajuan', [
                'req_mgr_produksi',
                'ditolak_mgr_produksi',
                'req_mgr_dukungan',
                'ditolak_mgr_dukungan',
                'req_akuntan',
                'ditolak_akuntan',
                'disetujui'
            ])->default('req_mgr_produksi');
            \$table->dateTime('tanggal_diajukan')->nullable();
            \$table->timestamp('disetujui_mgr_produksi')->nullable();
            \$table->timestamp('disetujui_mgr_dukungan')->nullable();
            \$table->timestamp('disetujui_akuntan')->nullable();
            \$table->text('alasan_ditolak')->nullable();
            \$table->timestamp('ditolak_pada')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_upah_pengajuan');
    }
};
PHP,

    'create_pembangunan_kawasan_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan', function (Blueprint \$table) {
            \$table->id();
            \$table->string('nama');
            \$table->foreignId('perumahaan_id')->constrained('perumahaan')->cascadeOnDelete();
            \$table->foreignId('pengawas_kawasan')->nullable()->constrained('users')->cascadeOnDelete();
            \$table->timestamp('tanggal_mulai')->nullable();
            \$table->timestamp('tanggal_selesai')->nullable();
            \$table->enum('status_pembangunan', ['pending', 'proses', 'selesai', 'selesai dengan catatan'])->default('pending');
            \$table->text('catatan')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan');
    }
};
PHP,

    'create_pembangunan_kawasan_barang_order_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_barang_order', function (Blueprint \$group) {
            \$group->id();
            \$group->foreignId('pembangunan_kawasan_id')->constrained('pembangunan_kawasan')->cascadeOnDelete();
            \$group->enum('jenis_order', ['stock', 'direct'])->default('stock');
            \$group->text('catatan')->nullable();
            \$group->dateTime('tanggal_diajukan');
            \$group->enum('status_order', ['diproses', 'selesai', 'ditolak', 'pengembalian'])->default('diproses');
            \$group->dateTime('tanggal_selesai')->nullable();
            \$group->foreignId('created_by')->constrained('users');
            \$group->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_barang_order');
    }
};
PHP,

    'create_pembangunan_kawasan_barang_order_detail_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_barang_order_detail', function (Blueprint \$group) {
            \$group->id();
            \$group->foreignId('order_id')->constrained('pembangunan_kawasan_barang_order', 'id', 'pkbod_order_fk')->onDelete('cascade');
            \$group->foreignId('barang_id')->nullable()->constrained('master_barang')->onDelete('set null');
            \$group->foreignId('satuan_id')->nullable()->constrained('master_satuan')->onDelete('set null');
            \$group->unsignedBigInteger('ubs_id')->nullable();

            \$group->decimal('jumlah_input', 18, 3);
            \$group->string('nama_barang')->nullable();
            \$group->string('satuan')->nullable();
            \$group->decimal('jumlah_base', 18, 3);

            \$group->boolean('konfirmasi')->default(false);

            \$group->decimal('jumlah_return', 18, 3)->default(0);
            \$group->text('keterangan_return')->nullable();

            \$group->decimal('harga_satuan_snapshot', 18, 2)->nullable();
            \$group->decimal('harga_total_snapshot', 18, 2)->nullable();

            \$group->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_barang_order_detail');
    }
};
PHP,

    'create_pembangunan_kawasan_barang_returns_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_barang_returns', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('pembangunan_kawasan_id')->constrained('pembangunan_kawasan', 'id', 'pkbr_kawasan_fk')->onDelete('cascade');
            \$table->foreignId('order_id')->constrained('pembangunan_kawasan_barang_order', 'id', 'pkbr_order_fk')->onDelete('cascade');
            \$table->text('alasan_return')->nullable();
            \$table->enum('status_return', ['pending', 'disetujui', 'ditolak'])->default('pending');
            \$table->foreignId('created_by')->constrained('users');
            \$table->dateTime('tanggal_return');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_barang_returns');
    }
};
PHP,

    'create_pembangunan_kawasan_barang_return_details_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_barang_return_details', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('return_id')->constrained('pembangunan_kawasan_barang_returns', 'id', 'pkbrd_return_fk')->onDelete('cascade');
            \$table->foreignId('order_detail_id')->constrained('pembangunan_kawasan_barang_order_detail', 'id', 'pkbrd_order_detail_fk')->onDelete('cascade');
            \$table->decimal('jumlah_return', 18, 3);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_barang_return_details');
    }
};
PHP,

    'create_pembangunan_kawasan_upah_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_upah', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('pembangunan_kawasan_id')->constrained('pembangunan_kawasan')->onDelete('cascade');
            \$table->string('nama_upah');
            \$table->decimal('total_nominal', 15, 2);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_upah');
    }
};
PHP,

    'create_pembangunan_kawasan_upah_pengajuans_table' => <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_upah_pengajuan', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('pembangunan_kawasan_id')->constrained('pembangunan_kawasan', 'id', 'pkup_kawasan_fk')->onDelete('cascade');
            \$table->string('nama_upah');
            \$table->decimal('nominal_diajukan', 18, 2);
            \$table->text('catatan_pengawas')->nullable();
            \$table->enum('status_pengajuan', [
                'req_mgr_produksi',
                'ditolak_mgr_produksi',
                'req_mgr_dukungan',
                'ditolak_mgr_dukungan',
                'req_akuntan',
                'ditolak_akuntan',
                'disetujui'
            ])->default('req_mgr_produksi');
            \$table->dateTime('tanggal_diajukan')->nullable();
            \$table->timestamp('disetujui_mgr_produksi')->nullable();
            \$table->timestamp('disetujui_mgr_dukungan')->nullable();
            \$table->timestamp('disetujui_akuntan')->nullable();
            \$table->text('alasan_ditolak')->nullable();
            \$table->timestamp('ditolak_pada')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_upah_pengajuan');
    }
};
PHP,
];

foreach ($contents as $suffix => $content) {
    $file = getFileBySuffix($files, $suffix);
    if ($file) {
        file_put_contents($file, $content);
        echo "Updated \$file\n";
    } else {
        echo "Not found: \$suffix\n";
    }
}
