<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100);
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->enum('peruntukan', ['yayasan', 'kas_putra', 'kas_putri', 'semua'])->default('semua');
            $table->timestamps();
        });

        Schema::create('keuangan_yayasan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 35)->unique();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_transaksi')->nullOnDelete();
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->decimal('nominal', 15, 2);
            $table->decimal('saldo_berjalan', 15, 2)->default(0);
            $table->date('tanggal')->useCurrent();
            $table->text('keterangan');
            $table->string('bukti_nota')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tanggal', 'jenis']);
        });

        Schema::create('kas_operasional', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 35)->unique();
            $table->enum('scope', ['putra', 'putri']);
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_transaksi')->nullOnDelete();
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->decimal('nominal', 15, 2);
            $table->decimal('saldo_berjalan', 15, 2)->default(0);
            $table->date('tanggal')->useCurrent();
            $table->text('keterangan');
            $table->string('bukti_nota')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('keuangan_yayasan_id')->nullable()->constrained('keuangan_yayasan')->nullOnDelete();
            $table->timestamps();

            $table->index(['scope', 'tanggal']);
            $table->index(['scope', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_operasional');
        Schema::dropIfExists('keuangan_yayasan');
        Schema::dropIfExists('kategori_transaksi');
    }
};
