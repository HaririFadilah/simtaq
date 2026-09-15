<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asrama', function (Blueprint $table) {
            $table->id();
            $table->string('nama_asrama', 100);
            $table->enum('gender', ['putra', 'putri']);
            $table->integer('kapasitas')->default(20);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('wali_santri', function (Blueprint $table) {
            $table->id();
            $table->string('nama_wali', 150);
            $table->enum('hubungan', ['ayah', 'ibu', 'wali'])->default('ayah');
            $table->string('no_hp', 25)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wali_santri');
        Schema::dropIfExists('asrama');
    }
};
