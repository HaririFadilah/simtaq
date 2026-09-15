<?php

use App\Models\CalonSantri;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('pengurus can login and access dashboard with financial summary', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'hariri@simtaq.test',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonStructure(['data' => ['token', 'user']]);

    $token = $response->json('data.token');

    $dashResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/dashboard');

    $dashResponse->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.top_metrics.santri.total', \App\Models\Santri::count())
                 ->assertJsonPath('data.top_metrics.psb.total', 18)
                 ->assertJsonPath('data.ringkasan_keuangan.kas_yayasan', 48750000)
                 ->assertJsonPath('data.ringkasan_keuangan.kas_putra', 12350000)
                 ->assertJsonPath('data.ringkasan_keuangan.kas_putri', 10870000);
});

test('ustadz can access santri and hafalan but denied from keuangan yayasan', function () {
    $login = $this->postJson('/api/login', [
        'email' => 'ustadz.ahmad@simtaq.test',
        'password' => 'password',
    ]);

    $token = $login->json('data.token');

    // Akses santri diizinkan
    $this->withHeader('Authorization', 'Bearer ' . $token)
         ->getJson('/api/santri')
         ->assertStatus(200);

    // Akses keuangan yayasan ditolak (403 Forbidden)
    $this->withHeader('Authorization', 'Bearer ' . $token)
         ->getJson('/api/keuangan')
         ->assertStatus(403);

    // Akses kas operasional ditolak (403 Forbidden)
    $this->withHeader('Authorization', 'Bearer ' . $token)
         ->getJson('/api/kas-operasional')
         ->assertStatus(403);
});

test('ketua santri putra can only view kas operasional putra', function () {
    $login = $this->postJson('/api/login', [
        'email' => 'ketua.putra@simtaq.test',
        'password' => 'password',
    ]);

    $token = $login->json('data.token');

    // Akses kas operasional putra
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                     ->getJson('/api/kas-operasional');

    $response->assertStatus(200)
             ->assertJsonPath('data.scope', 'putra')
             ->assertJsonPath('data.ringkasan.saldo_terkini', 12350000);

    // Akses keuangan yayasan ditolak
    $this->withHeader('Authorization', 'Bearer ' . $token)
         ->getJson('/api/keuangan')
         ->assertStatus(403);
});

test('psb 1-click conversion creates new santri without re-entry', function () {
    $user = User::where('role', 'pengurus')->first();

    $calon = CalonSantri::create([
        'no_pendaftaran' => 'PSB-TEST-001',
        'nama_lengkap' => 'Muhammad Thariq Ziyad',
        'nama_panggilan' => 'Thariq',
        'jenis_kelamin' => 'L',
        'asal_kota' => 'Tangerang',
        'nama_wali' => 'Drs. Suparman',
        'no_hp_wali' => '081234567800',
        'tanggal_daftar' => now(),
        'status_seleksi' => 'diterima',
    ]);

    $response = $this->actingAs($user)
                     ->postJson("/api/psb/{$calon->id}/convert");

    $response->assertStatus(200)
             ->assertJsonPath('success', true);

    $this->assertDatabaseHas('santri', [
        'nama_lengkap' => 'Muhammad Thariq Ziyad',
        'calon_santri_id' => $calon->id,
        'status' => 'aktif',
    ]);

    $this->assertDatabaseHas('calon_santri', [
        'id' => $calon->id,
        'is_converted' => true,
    ]);
});

test('public user can register as calon santri and check status without auth', function () {
    $registerData = [
        'nama_lengkap' => 'Ahmad Fathi Mubarak',
        'nama_panggilan' => 'Fathi',
        'jenis_kelamin' => 'L',
        'tempat_lahir' => 'Bandung',
        'tanggal_lahir' => '2012-05-10',
        'asal_kota' => 'Bandung',
        'asal_sekolah' => 'SDIT Al Amanah',
        'nama_wali' => 'H. Rahmat Mubarak',
        'no_hp_wali' => '081299887766',
        'alamat_wali' => 'Jl. Dago Asri No. 12 Bandung',
        'catatan_wali' => 'Ingin fokus tahfiz 30 juz',
    ];

    $response = $this->postJson('/api/public/psb/daftar', $registerData);

    $response->assertStatus(201)
             ->assertJsonPath('success', true)
             ->assertJsonStructure(['data' => ['no_pendaftaran', 'nama_lengkap', 'status_seleksi']]);

    $noPendaftaran = $response->json('data.no_pendaftaran');

    // Test check status
    $checkResponse = $this->getJson("/api/public/psb/cek/{$noPendaftaran}");
    $checkResponse->assertStatus(200)
                  ->assertJsonPath('success', true)
                  ->assertJsonPath('data.nama_lengkap', 'Ahmad Fathi Mubarak')
                  ->assertJsonPath('data.status_seleksi', 'diproses');
});
