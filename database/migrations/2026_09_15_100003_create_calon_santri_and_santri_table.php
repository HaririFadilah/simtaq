<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calon_santri', function (Blueprint $table) {
            $table->id();
            $table->string('no_pendaftaran', 30)->unique();
            $table->string('nama_lengkap', 150);
            $table->string('nama_panggilan', 50)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('asal_kota', 100)->nullable();
            $table->string('asal_sekolah', 150)->nullable();
            $table->string('nama_wali', 150);
            $table->string('no_hp_wali', 25);
            $table->text('alamat_wali')->nullable();
            $table->date('tanggal_daftar')->useCurrent();
            $table->enum('status_seleksi', ['diproses', 'wawancara', 'diterima', 'ditolak', 'cadangan'])->default('diproses');
            $table->foreignId('ustadz_pewawancara_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('hasil_wawancara')->nullable();
            $table->text('catatan_ustadz')->nullable();
            $table->boolean('is_converted')->default(false);
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('santri', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 30)->unique();
            $table->string('nama_lengkap', 150);
            $table->string('nama_panggilan', 50)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_masuk')->useCurrent();
            $table->enum('status', ['aktif', 'keluar', 'lulus'])->default('aktif');
            $table->foreignId('wali_id')->nullable()->constrained('wali_santri')->nullOnDelete();
            $table->foreignId('asrama_id')->nullable()->constrained('asrama')->nullOnDelete();
            $table->foreignId('calon_santri_id')->nullable()->constrained('calon_santri')->nullOnDelete();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri');
        Schema::dropIfExists('calon_santri');
    }
};
