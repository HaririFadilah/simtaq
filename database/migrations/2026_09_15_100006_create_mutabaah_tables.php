<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_mutabaah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan', 100); // Salat Subuh, Salat Dzuhur, dll.
            $table->string('kode', 50)->unique(); // subuh, dzuhur, ashar, maghrib, isya, tilawah, tahajud, dhuha
            $table->string('kategori', 50)->default('ibadah'); // ibadah, akhlak, kebersihan
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('mutabaah_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kegiatan_id')->constrained('kegiatan_mutabaah')->cascadeOnDelete();
            $table->foreignId('ketua_santri_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal')->useCurrent();
            $table->enum('status', ['hadir', 'tidak_hadir', 'izin', 'sakit'])->default('hadir');
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['santri_id', 'kegiatan_id', 'tanggal'], 'unique_mutabaah_santri_harian');
            $table->index(['tanggal', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutabaah_santri');
        Schema::dropIfExists('kegiatan_mutabaah');
    }
};
