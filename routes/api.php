<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DonasiController;
use App\Http\Controllers\Api\DonaturController;
use App\Http\Controllers\Api\HafalanController;
use App\Http\Controllers\Api\KasOperasionalController;
use App\Http\Controllers\Api\KeuanganYayasanController;
use App\Http\Controllers\Api\MutabaahController;
use App\Http\Controllers\Api\PerizinanController;
use App\Http\Controllers\Api\PsbController;
use App\Http\Controllers\Api\SantriController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SIMTAQ Yayasan Al Mukhlisin
|--------------------------------------------------------------------------
*/

// Public Routes (Auth & PSB Mandiri)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/public/psb/daftar', [PsbController::class, 'registerPublic']);
Route::get('/public/psb/cek/{no_pendaftaran}', [PsbController::class, 'checkPublicStatus']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Dashboard (All roles, response adapts automatically based on role/scope)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Master Asrama
    Route::get('/asrama', [SantriController::class, 'listAsrama']);

    // Modul Santri (Pengurus & Ustadz can manage, Ketua Santri can read)
    Route::get('/santri', [SantriController::class, 'index']);
    Route::get('/santri/{santri}', [SantriController::class, 'show']);
    Route::middleware('role:pengurus,ustadz')->group(function () {
        Route::post('/santri', [SantriController::class, 'store']);
        Route::put('/santri/{santri}', [SantriController::class, 'update']);
    });

    // Modul PSB (Pengurus & Ustadz Only)
    Route::middleware('role:pengurus,ustadz')->group(function () {
        Route::get('/psb', [PsbController::class, 'index']);
        Route::post('/psb', [PsbController::class, 'store']);
        Route::get('/psb/{calon_santri}', [PsbController::class, 'show']);
        Route::put('/psb/{calon_santri}', [PsbController::class, 'update']);
        Route::post('/psb/{calon_santri}/convert', [PsbController::class, 'convertToSantri']);
    });

    // Modul Perizinan Santri (Pengurus & Ustadz Only)
    Route::middleware('role:pengurus,ustadz')->group(function () {
        Route::get('/perizinan', [PerizinanController::class, 'index']);
        Route::post('/perizinan', [PerizinanController::class, 'store']);
        Route::post('/perizinan/{perizinan_santri}/kembali', [PerizinanController::class, 'konfirmasiKembali']);
    });

    // Modul Hafalan (Tahfiz) (Pengurus & Ustadz Only)
    Route::middleware('role:pengurus,ustadz')->group(function () {
        Route::get('/hafalan/setoran', [HafalanController::class, 'indexSetoran']);
        Route::post('/hafalan/setoran', [HafalanController::class, 'storeSetoran']);
        Route::get('/hafalan/murojaah', [HafalanController::class, 'indexMurojaah']);
        Route::post('/hafalan/murojaah', [HafalanController::class, 'storeMurojaah']);
        Route::get('/hafalan/evaluasi', [HafalanController::class, 'indexEvaluasi']);
        Route::post('/hafalan/evaluasi', [HafalanController::class, 'storeEvaluasi']);
    });

    // Modul Mutabaah Yaumiyah (Pengurus & Ketua Santri, Ustadz can view)
    Route::get('/mutabaah/kegiatan', [MutabaahController::class, 'getMasterKegiatan']);
    Route::get('/mutabaah/sheet', [MutabaahController::class, 'getDailySheet']);
    Route::get('/mutabaah/trends', [MutabaahController::class, 'getTrends']);
    Route::middleware('role:pengurus,ketua_santri')->group(function () {
        Route::post('/mutabaah/sheet', [MutabaahController::class, 'saveDailySheet']);
    });

    // Modul Donatur & Donasi (Pengurus Only)
    Route::middleware('role:pengurus')->group(function () {
        Route::get('/donatur/options', [DonaturController::class, 'options']);
        Route::apiResource('donatur', DonaturController::class);
        Route::apiResource('donasi', DonasiController::class)->only(['index', 'store']);
    });

    // Modul Keuangan Yayasan (Kas Besar) (Pengurus Only)
    Route::middleware('role:pengurus')->group(function () {
        Route::get('/keuangan/kategori', [KeuanganYayasanController::class, 'getKategori']);
        Route::get('/keuangan', [KeuanganYayasanController::class, 'index']);
        Route::post('/keuangan', [KeuanganYayasanController::class, 'store']);
    });

    // Modul Kas Operasional Santri (Kas Putra & Kas Putri) (Pengurus & Ketua Santri)
    Route::middleware('role:pengurus,ketua_santri')->group(function () {
        Route::get('/kas-operasional/kategori', [KasOperasionalController::class, 'getKategori']);
        Route::get('/kas-operasional', [KasOperasionalController::class, 'index']);
        Route::post('/kas-operasional', [KasOperasionalController::class, 'store']);
    });

    // Modul Manajemen Pengguna & Hak Akses (Pengurus Only)
    Route::middleware('role:pengurus')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});
