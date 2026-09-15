<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Target hafalan santri
        Schema::create('target_hafalan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->integer('target_halaman_per_hari')->default(1);
            $table->integer('total_target_juz')->default(30);
            $table->date('tanggal_mulai')->useCurrent();
            $table->date('target_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Setoran hafalan harian (berbasis halaman & juz)
        Schema::create('setoran_hafalan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal')->useCurrent();
            $table->unsignedTinyInteger('juz'); // 1-30
            $table->unsignedSmallInteger('halaman_mulai'); // 1-604
            $table->unsignedSmallInteger('halaman_selesai'); // 1-604
            $table->unsignedSmallInteger('total_halaman')->default(1);
            $table->string('surat_ayat_info')->nullable(); // e.g. "Al-Baqarah: 1-25"
            $table->enum('jenis_setoran', ['ziyadah', 'murojaah_harian', 'tasmi'])->default('ziyadah');
            $table->enum('kualitas', ['mutqin', 'jayyid', 'maqbul', 'ulang'])->default('mutqin');
            $table->unsignedTinyInteger('nilai')->default(100);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'tanggal']);
            $table->index('juz');
        });

        // Muroja'ah tuntas 1 juz
        Schema::create('murojaah_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('juz'); // 1-30
            $table->date('tanggal_murojaah')->useCurrent();
            $table->enum('status', ['tuntas', 'ulang'])->default('tuntas');
            $table->unsignedTinyInteger('nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'juz']);
        });

        // Evaluasi akbar kelipatan 5 juz (5, 10, 15, 20, 25, 30)
        Schema::create('evaluasi_hafalan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('penguji_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('kelipatan_juz'); // 5, 10, 15, 20, 25, 30
            $table->date('tanggal_evaluasi')->useCurrent();
            $table->decimal('nilai_kelancaran', 5, 2)->default(0);
            $table->decimal('nilai_tajwid', 5, 2)->default(0);
            $table->decimal('nilai_makhraj', 5, 2)->default(0);
            $table->decimal('nilai_total', 5, 2)->default(0);
            $table->enum('status_kelulusan', ['lulus_mumtaz', 'lulus_jayyid', 'mengulang'])->default('lulus_jayyid');
            $table->string('no_sertifikat', 50)->nullable();
            $table->text('catatan_penguji')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'kelipatan_juz']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_hafalan');
        Schema::dropIfExists('murojaah_santri');
        Schema::dropIfExists('setoran_hafalan');
        Schema::dropIfExists('target_hafalan');
    }
};
