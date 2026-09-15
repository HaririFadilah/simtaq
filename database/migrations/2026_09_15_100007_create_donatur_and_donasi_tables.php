<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donatur', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->enum('tipe_donatur', ['perorangan', 'lembaga', 'hamba_allah'])->default('perorangan');
            $table->string('no_hp', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->enum('kategori', ['rutin', 'insidental'])->default('insidental');
            $table->timestamps();
        });

        Schema::create('donasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_donasi', 30)->unique();
            $table->foreignId('donatur_id')->nullable()->constrained('donatur')->nullOnDelete();
            $table->string('nama_donatur_manual', 150)->nullable(); // untuk anonim / hamba Allah
            $table->enum('jenis_donasi', ['uang', 'makanan', 'barang'])->default('uang');
            $table->decimal('nominal', 15, 2)->nullable(); // untuk jenis uang
            $table->string('nama_barang', 150)->nullable(); // untuk makanan/barang
            $table->string('jumlah_barang', 100)->nullable(); // e.g. "50 Kg Beras", "100 Pcs Nasi Kotak"
            $table->date('tanggal_donasi')->useCurrent();
            $table->text('keterangan')->nullable();
            $table->string('bukti_transaksi')->nullable();
            $table->foreignId('penerima_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tanggal_donasi', 'jenis_donasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donasi');
        Schema::dropIfExists('donatur');
    }
};
