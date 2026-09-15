<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modul', 50); // PSB, Santri, Hafalan, Perizinan, Mutabaah, Keuangan, Kas, Donasi
            $table->string('aksi', 50); // CREATE, UPDATE, DELETE, VERIFIKASI, SETORAN, dll.
            $table->string('judul', 150); // e.g. "Santri baru diterima", "Izin santri dikonfirmasi"
            $table->text('deskripsi'); // e.g. "Ahmad Fauzi (NIS 20260045)"
            $table->string('icon', 50)->default('check_circle');
            $table->string('warna_badge', 30)->default('positive'); // positive, info, warning, negative
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'modul']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
