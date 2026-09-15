<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['pengurus', 'ustadz', 'ketua_santri'])->default('pengurus')->after('email');
            $table->enum('gender_scope', ['semua', 'putra', 'putri'])->default('semua')->after('role');
            $table->string('no_hp', 20)->nullable()->after('gender_scope');
            $table->string('foto')->nullable()->after('no_hp');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'gender_scope', 'no_hp', 'foto', 'status']);
        });
    }
};
