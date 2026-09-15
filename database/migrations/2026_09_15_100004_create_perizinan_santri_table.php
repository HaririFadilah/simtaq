<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perizinan_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis_izin', ['pulang', 'sakit', 'keperluan_mendesak', 'kegiatan_luar', 'lainnya']);
            $table->text('alasan');
            $table->dateTime('waktu_mulai');
            $table->dateTime('batas_kembali');
            $table->dateTime('waktu_kembali')->nullable();
            $table->enum('status', ['sedang_izin', 'telah_kembali', 'terlambat'])->default('sedang_izin');
            $table->foreignId('ustadz_konfirmasi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_kembali')->nullable();
            $table->timestamps();

            $table->index(['status', 'batas_kembali']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perizinan_santri');
    }
};
