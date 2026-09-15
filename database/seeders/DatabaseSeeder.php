<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Asrama;
use App\Models\CalonSantri;
use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\EvaluasiHafalan;
use App\Models\KasOperasional;
use App\Models\KategoriTransaksi;
use App\Models\KegiatanMutabaah;
use App\Models\KeuanganYayasan;
use App\Models\MurojaahSantri;
use App\Models\MutabaahSantri;
use App\Models\PerizinanSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use App\Models\TargetHafalan;
use App\Models\User;
use App\Models\WaliSantri;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. SEED USERS
        $pengurus = User::create([
            'name' => 'Muhamad Hariri Fadilah',
            'email' => 'hariri@simtaq.test',
            'password' => Hash::make('password'),
            'role' => 'pengurus',
            'gender_scope' => 'semua',
            'no_hp' => '081234567890',
            'status' => 'aktif',
        ]);

        $ustadzAhmad = User::create([
            'name' => 'Ustadz Ahmad Fauzan, Al-Hafizh',
            'email' => 'ustadz.ahmad@simtaq.test',
            'password' => Hash::make('password'),
            'role' => 'ustadz',
            'gender_scope' => 'putra',
            'no_hp' => '081234567891',
            'status' => 'aktif',
        ]);

        $ustadzahMaryam = User::create([
            'name' => 'Ustadzah Maryam, S.Pd.I',
            'email' => 'ustadzah.maryam@simtaq.test',
            'password' => Hash::make('password'),
            'role' => 'ustadz',
            'gender_scope' => 'putri',
            'no_hp' => '081234567892',
            'status' => 'aktif',
        ]);

        $ketuaPutra = User::create([
            'name' => 'Farhan Robbani',
            'email' => 'ketua.putra@simtaq.test',
            'password' => Hash::make('password'),
            'role' => 'ketua_santri',
            'gender_scope' => 'putra',
            'no_hp' => '081234567893',
            'status' => 'aktif',
        ]);

        $ketuaPutri = User::create([
            'name' => 'Aisyah Humaira',
            'email' => 'ketua.putri@simtaq.test',
            'password' => Hash::make('password'),
            'role' => 'ketua_santri',
            'gender_scope' => 'putri',
            'no_hp' => '081234567894',
            'status' => 'aktif',
        ]);

        // 2. SEED ASRAMA
        $asramaAbuBakar = Asrama::create([
            'nama_asrama' => 'Asrama Abu Bakar Ash-Shiddiq',
            'gender' => 'putra',
            'kapasitas' => 50,
            'keterangan' => 'Gedung A Lantai 1',
        ]);

        $asramaUmar = Asrama::create([
            'nama_asrama' => 'Asrama Umar bin Khattab',
            'gender' => 'putra',
            'kapasitas' => 50,
            'keterangan' => 'Gedung A Lantai 2',
        ]);

        $asramaKhadijah = Asrama::create([
            'nama_asrama' => 'Asrama Khadijah Al-Kubra',
            'gender' => 'putri',
            'kapasitas' => 40,
            'keterangan' => 'Gedung B Lantai 1',
        ]);

        $asramaAisyah = Asrama::create([
            'nama_asrama' => 'Asrama Aisyah Ash-Shiddiqah',
            'gender' => 'putri',
            'kapasitas' => 40,
            'keterangan' => 'Gedung B Lantai 2',
        ]);

        // 3. SEED KEGIATAN MUTABAAH
        $kegiatans = [
            ['nama_kegiatan' => 'Salat Subuh', 'kode' => 'subuh', 'kategori' => 'ibadah', 'urutan' => 1],
            ['nama_kegiatan' => 'Salat Dzuhur', 'kode' => 'dzuhur', 'kategori' => 'ibadah', 'urutan' => 2],
            ['nama_kegiatan' => 'Salat Ashar', 'kode' => 'ashar', 'kategori' => 'ibadah', 'urutan' => 3],
            ['nama_kegiatan' => 'Salat Maghrib', 'kode' => 'maghrib', 'kategori' => 'ibadah', 'urutan' => 4],
            ['nama_kegiatan' => 'Salat Isya', 'kode' => 'isya', 'kategori' => 'ibadah', 'urutan' => 5],
            ['nama_kegiatan' => 'Tilawah Al-Qur\'an', 'kode' => 'tilawah', 'kategori' => 'ibadah', 'urutan' => 6],
            ['nama_kegiatan' => 'Salat Tahajud', 'kode' => 'tahajud', 'kategori' => 'ibadah', 'urutan' => 7],
            ['nama_kegiatan' => 'Salat Dhuha', 'kode' => 'dhuha', 'kategori' => 'ibadah', 'urutan' => 8],
        ];

        foreach ($kegiatans as $k) {
            KegiatanMutabaah::create($k);
        }

        // 4. SEED KATEGORI TRANSAKSI
        $kategoriInfaq = KategoriTransaksi::create(['nama_kategori' => 'Infaq & Donasi', 'jenis' => 'pemasukan', 'peruntukan' => 'yayasan']);
        $kategoriDropping = KategoriTransaksi::create(['nama_kategori' => 'Alokasi Kas Operasional', 'jenis' => 'pengeluaran', 'peruntukan' => 'yayasan']);
        $kategoriGaji = KategoriTransaksi::create(['nama_kategori' => 'Honor & Kafalah Asatidz', 'jenis' => 'pengeluaran', 'peruntukan' => 'yayasan']);
        $kategoriUtilitas = KategoriTransaksi::create(['nama_kategori' => 'Listrik, Air & WiFi', 'jenis' => 'pengeluaran', 'peruntukan' => 'yayasan']);

        $kategoriDroppingMasuk = KategoriTransaksi::create(['nama_kategori' => 'Dropping Dana Yayasan', 'jenis' => 'pemasukan', 'peruntukan' => 'semua']);
        $kategoriKonsumsi = KategoriTransaksi::create(['nama_kategori' => 'Konsumsi & Ekstra Santri', 'jenis' => 'pengeluaran', 'peruntukan' => 'semua']);
        $kategoriKebersihan = KategoriTransaksi::create(['nama_kategori' => 'Alat & Kebersihan Asrama', 'jenis' => 'pengeluaran', 'peruntukan' => 'semua']);
        $kategoriMedis = KategoriTransaksi::create(['nama_kategori' => 'Obat & P3K Santri', 'jenis' => 'pengeluaran', 'peruntukan' => 'semua']);

        // 5. SEED KEUANGAN YAYASAN & KAS OPERASIONAL (Match Mockup: Yayasan 48.750.000, Putra 12.350.000, Putri 10.870.000)
        KeuanganYayasan::create([
            'kode_transaksi' => 'YYS-' . $now->format('Ym') . '-001',
            'kategori_id' => $kategoriInfaq->id,
            'jenis' => 'pemasukan',
            'nominal' => 60000000,
            'saldo_berjalan' => 60000000,
            'tanggal' => $now->copy()->subDays(10),
            'keterangan' => 'Saldo Awal Bulan Yayasan Al Mukhlisin',
            'user_id' => $pengurus->id,
        ]);

        $transDroppingPa = KeuanganYayasan::create([
            'kode_transaksi' => 'YYS-' . $now->format('Ym') . '-002',
            'kategori_id' => $kategoriDropping->id,
            'jenis' => 'pengeluaran',
            'nominal' => 15000000,
            'saldo_berjalan' => 45000000,
            'tanggal' => $now->copy()->subDays(5),
            'keterangan' => 'Alokasi Dana Kas Operasional Putra',
            'user_id' => $pengurus->id,
        ]);

        $transDroppingPi = KeuanganYayasan::create([
            'kode_transaksi' => 'YYS-' . $now->format('Ym') . '-003',
            'kategori_id' => $kategoriDropping->id,
            'jenis' => 'pengeluaran',
            'nominal' => 12000000,
            'saldo_berjalan' => 33000000,
            'tanggal' => $now->copy()->subDays(5),
            'keterangan' => 'Alokasi Dana Kas Operasional Putri',
            'user_id' => $pengurus->id,
        ]);

        KeuanganYayasan::create([
            'kode_transaksi' => 'YYS-' . $now->format('Ym') . '-004',
            'kategori_id' => $kategoriInfaq->id,
            'jenis' => 'pemasukan',
            'nominal' => 15750000,
            'saldo_berjalan' => 48750000, // Matching Mockup: Rp 48.750.000
            'tanggal' => $now->copy()->subDays(1),
            'keterangan' => 'Infaq Donatur Tetap Yayasan',
            'user_id' => $pengurus->id,
        ]);

        // Kas Operasional Putra (Saldo: 12.350.000)
        KasOperasional::create([
            'kode_transaksi' => 'KAS-PA-' . $now->format('Ym') . '-001',
            'scope' => 'putra',
            'kategori_id' => $kategoriDroppingMasuk->id,
            'jenis' => 'pemasukan',
            'nominal' => 15000000,
            'saldo_berjalan' => 15000000,
            'tanggal' => $now->copy()->subDays(5),
            'keterangan' => 'Dropping Dana dari Yayasan',
            'user_id' => $pengurus->id,
            'keuangan_yayasan_id' => $transDroppingPa->id,
        ]);

        KasOperasional::create([
            'kode_transaksi' => 'KAS-PA-' . $now->format('Ym') . '-002',
            'scope' => 'putra',
            'kategori_id' => $kategoriKonsumsi->id,
            'jenis' => 'pengeluaran',
            'nominal' => 2650000,
            'saldo_berjalan' => 12350000, // Matching Mockup: Rp 12.350.000
            'tanggal' => $now->copy()->subDays(2),
            'keterangan' => 'Belanja konsumsi & buah ekstra santri putra',
            'user_id' => $ketuaPutra->id,
        ]);

        // Kas Operasional Putri (Saldo: 10.870.000)
        KasOperasional::create([
            'kode_transaksi' => 'KAS-PI-' . $now->format('Ym') . '-001',
            'scope' => 'putri',
            'kategori_id' => $kategoriDroppingMasuk->id,
            'jenis' => 'pemasukan',
            'nominal' => 12000000,
            'saldo_berjalan' => 12000000,
            'tanggal' => $now->copy()->subDays(5),
            'keterangan' => 'Dropping Dana dari Yayasan',
            'user_id' => $pengurus->id,
            'keuangan_yayasan_id' => $transDroppingPi->id,
        ]);

        KasOperasional::create([
            'kode_transaksi' => 'KAS-PI-' . $now->format('Ym') . '-002',
            'scope' => 'putri',
            'kategori_id' => $kategoriKebersihan->id,
            'jenis' => 'pengeluaran',
            'nominal' => 1130000,
            'saldo_berjalan' => 10870000, // Matching Mockup: Rp 10.870.000
            'tanggal' => $now->copy()->subDays(2),
            'keterangan' => 'Pembelian peralatan kebersihan & obat P3K asrama putri',
            'user_id' => $ketuaPutri->id,
        ]);

        // 6. SEED DONATUR & DONASI (Match Mockup: Bapak H. Abdullah 5jt, Ibu Siti Aminah 2.5jt, PT. Barokah 10jt, Bpk. Hasan 1jt)
        $donatur1 = Donatur::create(['nama' => 'Bapak H. Abdullah', 'tipe_donatur' => 'perorangan', 'no_hp' => '081299881122', 'kategori' => 'rutin']);
        $donatur2 = Donatur::create(['nama' => 'Ibu Siti Aminah', 'tipe_donatur' => 'perorangan', 'no_hp' => '081377889900', 'kategori' => 'rutin']);
        $donatur3 = Donatur::create(['nama' => 'PT. Barokah Sejahtera', 'tipe_donatur' => 'lembaga', 'no_hp' => '02188990011', 'kategori' => 'insidental']);
        $donatur4 = Donatur::create(['nama' => 'Bpk. Hasan Basri', 'tipe_donatur' => 'perorangan', 'no_hp' => '085211223344', 'kategori' => 'insidental']);

        Donasi::create([
            'kode_donasi' => 'DNS-2026-001',
            'donatur_id' => $donatur1->id,
            'jenis_donasi' => 'uang',
            'nominal' => 5000000,
            'tanggal_donasi' => $now->copy()->subDays(2),
            'keterangan' => 'Donasi operasional santri tahfiz',
            'penerima_id' => $pengurus->id,
        ]);

        Donasi::create([
            'kode_donasi' => 'DNS-2026-002',
            'donatur_id' => $donatur2->id,
            'jenis_donasi' => 'uang',
            'nominal' => 2500000,
            'tanggal_donasi' => $now->copy()->subDays(4),
            'keterangan' => 'Sedekah jumat berkah',
            'penerima_id' => $pengurus->id,
        ]);

        Donasi::create([
            'kode_donasi' => 'DNS-2026-003',
            'donatur_id' => $donatur3->id,
            'jenis_donasi' => 'uang',
            'nominal' => 10000000,
            'tanggal_donasi' => $now->copy()->subDays(6),
            'keterangan' => 'CSR Yayasan Barokah Peduli Umat',
            'penerima_id' => $pengurus->id,
        ]);

        Donasi::create([
            'kode_donasi' => 'DNS-2026-004',
            'donatur_id' => $donatur4->id,
            'jenis_donasi' => 'uang',
            'nominal' => 1000000,
            'tanggal_donasi' => $now->copy()->subDays(9),
            'keterangan' => 'Infaq atas nama keluarga',
            'penerima_id' => $pengurus->id,
        ]);

        Donasi::create([
            'kode_donasi' => 'DNS-2026-005',
            'donatur_id' => $donatur1->id,
            'jenis_donasi' => 'makanan',
            'nama_barang' => 'Beras Pandan Wangi & Kurma',
            'jumlah_barang' => '100 Kg & 5 Dus Kurma Sukari',
            'tanggal_donasi' => $now->copy()->subDays(1),
            'keterangan' => 'Konsumsi ifthar santri sunnah kamis',
            'penerima_id' => $pengurus->id,
        ]);

        // 7. SEED PSB (Match Mockup: 18 Calon Santri: 8 Diproses, 7 Diterima, 2 Ditolak, 1 Cadangan)
        $psbData = [
            ['nama_lengkap' => 'Ahmad Fauzi', 'asal_kota' => 'Jakarta', 'status' => 'diproses', 'tgl' => 3],
            ['nama_lengkap' => 'Siti Nurhaliza', 'asal_kota' => 'Depok', 'status' => 'wawancara', 'tgl' => 4],
            ['nama_lengkap' => 'Rizky Maulana', 'asal_kota' => 'Bogor', 'status' => 'diterima', 'tgl' => 5],
            ['nama_lengkap' => 'Dewi Lestari', 'asal_kota' => 'Bekasi', 'status' => 'ditolak', 'tgl' => 6],
            ['nama_lengkap' => 'Agus Setiawan', 'asal_kota' => 'Tangerang', 'status' => 'cadangan', 'tgl' => 7],
            ['nama_lengkap' => 'Muhammad Bilal', 'asal_kota' => 'Bandung', 'status' => 'diproses', 'tgl' => 2],
            ['nama_lengkap' => 'Zahra Amalia', 'asal_kota' => 'Jakarta Timur', 'status' => 'diproses', 'tgl' => 1],
            ['nama_lengkap' => 'Farid Rahman', 'asal_kota' => 'Sukabumi', 'status' => 'diproses', 'tgl' => 2],
            ['nama_lengkap' => 'Fathir Azzam', 'asal_kota' => 'Bekasi', 'status' => 'diproses', 'tgl' => 3],
            ['nama_lengkap' => 'Nayla Khansa', 'asal_kota' => 'Depok', 'status' => 'diproses', 'tgl' => 4],
            ['nama_lengkap' => 'Rayhan Pratama', 'asal_kota' => 'Bogor', 'status' => 'diproses', 'tgl' => 5],
            ['nama_lengkap' => 'Salma Hanifa', 'asal_kota' => 'Jakarta Selatan', 'status' => 'diproses', 'tgl' => 6],
            ['nama_lengkap' => 'Irfan Hakim', 'asal_kota' => 'Tangerang Selatan', 'status' => 'diterima', 'tgl' => 7],
            ['nama_lengkap' => 'Haidar Ali', 'asal_kota' => 'Cirebon', 'status' => 'diterima', 'tgl' => 8],
            ['nama_lengkap' => 'Fatima Zahro', 'asal_kota' => 'Semarang', 'status' => 'diterima', 'tgl' => 8],
            ['nama_lengkap' => 'Zaidan Alif', 'asal_kota' => 'Surabaya', 'status' => 'diterima', 'tgl' => 9],
            ['nama_lengkap' => 'Safira Azzahra', 'asal_kota' => 'Malang', 'status' => 'diterima', 'tgl' => 9],
            ['nama_lengkap' => 'Bintang Ramadhan', 'asal_kota' => 'Serang', 'status' => 'ditolak', 'tgl' => 10],
        ];

        foreach ($psbData as $idx => $p) {
            $jk = in_array($p['nama_lengkap'], ['Siti Nurhaliza', 'Dewi Lestari', 'Zahra Amalia', 'Nayla Khansa', 'Salma Hanifa', 'Fatima Zahro', 'Safira Azzahra']) ? 'P' : 'L';
            CalonSantri::create([
                'no_pendaftaran' => 'PSB-2026-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'nama_lengkap' => $p['nama_lengkap'],
                'nama_panggilan' => explode(' ', $p['nama_lengkap'])[0],
                'jenis_kelamin' => $jk,
                'asal_kota' => $p['asal_kota'],
                'asal_sekolah' => 'SD / SMP Islam ' . $p['asal_kota'],
                'nama_wali' => 'Wali dari ' . $p['nama_lengkap'],
                'no_hp_wali' => '08' . rand(111111111, 999999999),
                'tanggal_daftar' => $now->copy()->subDays($p['tgl']),
                'status_seleksi' => $p['status'],
                'ustadz_pewawancara_id' => $ustadzAhmad->id,
                'hasil_wawancara' => 'Kemampuan membaca Al-Qur\'an cukup baik, tajwid lancar dan memiliki motivasi menghafal kuat.',
            ]);
        }

        // 8. SEED SANTRI (Total 124: Aktif 118, Keluar 4, Lulus 2)
        $waliSample = WaliSantri::create([
            'nama_wali' => 'H. Mulyadi Saputra',
            'hubungan' => 'ayah',
            'no_hp' => '081288990011',
            'pekerjaan' => 'Wiraswasta',
            'alamat' => 'Jl. Kebon Jeruk No. 12, Jakarta Barat',
        ]);

        $featuredSantri = [
            ['nama' => 'Ahmad Syafri', 'gender' => 'L', 'nis' => '20240001', 'status' => 'aktif', 'asrama_id' => $asramaAbuBakar->id, 'juz' => 3],
            ['nama' => 'M. Rizki Pratama', 'gender' => 'L', 'nis' => '20240002', 'status' => 'aktif', 'asrama_id' => $asramaAbuBakar->id, 'juz' => 5],
            ['nama' => 'Ahmad Fauzi', 'gender' => 'L', 'nis' => '20260045', 'status' => 'aktif', 'asrama_id' => $asramaUmar->id, 'juz' => 1],
            ['nama' => 'Naufal Izzudin', 'gender' => 'L', 'nis' => '20230010', 'status' => 'aktif', 'asrama_id' => $asramaAbuBakar->id, 'juz' => 15],
            ['nama' => 'Syamil Basayev', 'gender' => 'L', 'nis' => '20230015', 'status' => 'aktif', 'asrama_id' => $asramaUmar->id, 'juz' => 20],
            ['nama' => 'Zahira Fairuz', 'gender' => 'P', 'nis' => '20240030', 'status' => 'aktif', 'asrama_id' => $asramaKhadijah->id, 'juz' => 7],
            ['nama' => 'Khadijah Salsabila', 'gender' => 'P', 'nis' => '20230005', 'status' => 'aktif', 'asrama_id' => $asramaAisyah->id, 'juz' => 30],
            ['nama' => 'Anisa Rahmawati', 'gender' => 'P', 'nis' => '20220001', 'status' => 'lulus', 'asrama_id' => $asramaKhadijah->id, 'juz' => 30],
            ['nama' => 'Muhammad Fikri', 'gender' => 'L', 'nis' => '20220002', 'status' => 'lulus', 'asrama_id' => $asramaAbuBakar->id, 'juz' => 30],
            ['nama' => 'Deni Kurniawan', 'gender' => 'L', 'nis' => '20240088', 'status' => 'keluar', 'asrama_id' => $asramaUmar->id, 'juz' => 2],
            ['nama' => 'Fajar Sidik', 'gender' => 'L', 'nis' => '20240089', 'status' => 'keluar', 'asrama_id' => $asramaUmar->id, 'juz' => 1],
            ['nama' => 'Laila Majnun', 'gender' => 'P', 'nis' => '20240090', 'status' => 'keluar', 'asrama_id' => $asramaKhadijah->id, 'juz' => 2],
            ['nama' => 'Tiara Lestari', 'gender' => 'P', 'nis' => '20240091', 'status' => 'keluar', 'asrama_id' => $asramaAisyah->id, 'juz' => 1],
        ];

        foreach ($featuredSantri as $s) {
            $created = Santri::create([
                'nis' => $s['nis'],
                'nama_lengkap' => $s['nama'],
                'nama_panggilan' => explode(' ', $s['nama'])[0],
                'jenis_kelamin' => $s['gender'],
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => Carbon::now()->subYears(14),
                'alamat' => 'Pondok Pesantren Al Mukhlisin',
                'tanggal_masuk' => Carbon::now()->subYears(2),
                'status' => $s['status'],
                'wali_id' => $waliSample->id,
                'asrama_id' => $s['asrama_id'],
            ]);

            TargetHafalan::create([
                'santri_id' => $created->id,
                'target_halaman_per_hari' => 1,
                'total_target_juz' => 30,
                'tanggal_mulai' => $created->tanggal_masuk,
            ]);

            // Add sample setoran
            SetoranHafalan::create([
                'santri_id' => $created->id,
                'ustadz_id' => $ustadzAhmad->id,
                'tanggal' => $now->copy()->subDays(rand(0, 3)),
                'juz' => $s['juz'],
                'halaman_mulai' => 1,
                'halaman_selesai' => 20,
                'total_halaman' => 20,
                'surat_ayat_info' => 'Juz ' . $s['juz'] . ' Lengkap',
                'jenis_setoran' => 'ziyadah',
                'kualitas' => 'mutqin',
                'nilai' => 95,
            ]);
        }

        // Fill remaining active santri up to 118 active (Total 118 active + 4 keluar + 2 lulus = 124 santri)
        // Current active: 7. Need 111 more.
        $countNeeded = 111;
        $santriNames = [
            'Abdullah', 'Abdurrahman', 'Ali', 'Umar', 'Usman', 'Zubair', 'Thalhah', 'Saad', 'Said', 'Abu Ubaidah',
            'Hamzah', 'Abbas', 'Bilal', 'Salman', 'Ammar', 'Miqdad', 'Hudzaifah', 'Muadz', 'Ubay', 'Zaid',
            'Aisyah', 'Fathimah', 'Khadijah', 'Hafshah', 'Zainab', 'Ummu Salamah', 'Juwairiyah', 'Shafiyah', 'Maimunah', 'Maryam'
        ];

        for ($i = 1; $i <= $countNeeded; $i++) {
            $isFemale = ($i % 3 === 0);
            $gender = $isFemale ? 'P' : 'L';
            $namePrefix = $santriNames[($i - 1) % count($santriNames)];
            $fullName = $namePrefix . ' Al-Fatih ' . ($i);
            $asrama = $isFemale ? ($i % 2 === 0 ? $asramaKhadijah : $asramaAisyah) : ($i % 2 === 0 ? $asramaAbuBakar : $asramaUmar);

            $snt = Santri::create([
                'nis' => '2025' . str_pad($i + 100, 4, '0', STR_PAD_LEFT),
                'nama_lengkap' => $fullName,
                'nama_panggilan' => $namePrefix,
                'jenis_kelamin' => $gender,
                'tempat_lahir' => 'Bogor',
                'tanggal_lahir' => Carbon::now()->subYears(13),
                'alamat' => 'Komplek Al Mukhlisin',
                'tanggal_masuk' => Carbon::now()->subYear(),
                'status' => 'aktif',
                'wali_id' => $waliSample->id,
                'asrama_id' => $asrama->id,
            ]);

            TargetHafalan::create([
                'santri_id' => $snt->id,
                'target_halaman_per_hari' => 1,
                'total_target_juz' => 30,
                'tanggal_mulai' => $snt->tanggal_masuk,
            ]);
        }

        // 9. SEED PERIZINAN (Match Mockup: M. Rizki Pratama kembali 15:30)
        $santriIzin = Santri::where('nama_lengkap', 'M. Rizki Pratama')->first();
        if ($santriIzin) {
            PerizinanSantri::create([
                'santri_id' => $santriIzin->id,
                'ustadz_id' => $ustadzAhmad->id,
                'jenis_izin' => 'keperluan_mendesak',
                'alasan' => 'Pemeriksaan kesehatan gigi di klinik mitra',
                'waktu_mulai' => $now->copy()->setTime(8, 0),
                'batas_kembali' => $now->copy()->setTime(15, 30),
                'waktu_kembali' => $now->copy()->setTime(15, 10),
                'status' => 'telah_kembali',
                'ustadz_konfirmasi_id' => $ustadzAhmad->id,
                'catatan_kembali' => 'Kembali tepat waktu dengan membawa surat keterangan dokter.',
            ]);
        }

        // 10. SEED MUTABAAH YAUMIYAH HARI INI
        $activeSantris = Santri::where('status', 'aktif')->take(30)->get();
        $allKegiatans = KegiatanMutabaah::all();

        foreach ($activeSantris as $snt) {
            foreach ($allKegiatans as $kgt) {
                MutabaahSantri::create([
                    'santri_id' => $snt->id,
                    'kegiatan_id' => $kgt->id,
                    'ketua_santri_id' => ($snt->jenis_kelamin === 'L') ? $ketuaPutra->id : $ketuaPutri->id,
                    'tanggal' => $now->toDateString(),
                    'status' => (rand(1, 10) > 1) ? 'hadir' : 'izin', // 90%+ compliance
                ]);
            }
        }

        // 11. SEED ACTIVITY LOGS (Match Mockup "Aktivitas Terbaru")
        ActivityLog::create([
            'user_id' => $pengurus->id,
            'modul' => 'PSB',
            'aksi' => 'TERIMA',
            'judul' => 'Santri baru diterima',
            'deskripsi' => 'Ahmad Fauzi (NIS 20260045)',
            'icon' => 'check_circle',
            'warna_badge' => 'positive',
            'created_at' => $now->copy()->setTime(10, 24),
        ]);

        ActivityLog::create([
            'user_id' => $ustadzAhmad->id,
            'modul' => 'Perizinan',
            'aksi' => 'KONFIRMASI',
            'judul' => 'Izin santri dikonfirmasi',
            'deskripsi' => 'M. Rizki Pratama (Kembali: 15:30)',
            'icon' => 'person',
            'warna_badge' => 'info',
            'created_at' => $now->copy()->setTime(9, 12),
        ]);

        ActivityLog::create([
            'user_id' => $ustadzAhmad->id,
            'modul' => 'Hafalan',
            'aksi' => 'SETORAN',
            'judul' => 'Setoran hafalan',
            'deskripsi' => 'Ahmad Syafri (3 Juz)',
            'icon' => 'menu_book',
            'warna_badge' => 'warning',
            'created_at' => $now->copy()->setTime(8, 45),
        ]);

        ActivityLog::create([
            'user_id' => $pengurus->id,
            'modul' => 'Donasi',
            'aksi' => 'TERIMA',
            'judul' => 'Donasi baru',
            'deskripsi' => 'Bapak H. Abdullah (Rp 5.000.000)',
            'icon' => 'favorite',
            'warna_badge' => 'negative',
            'created_at' => $now->copy()->setTime(7, 50),
        ]);

        ActivityLog::create([
            'user_id' => $ketuaPutra->id,
            'modul' => 'Mutabaah',
            'aksi' => 'CHECKLIST',
            'judul' => 'Mutabaah harian',
            'deskripsi' => 'Asrama Putra (92%)',
            'icon' => 'verified',
            'warna_badge' => 'positive',
            'created_at' => $now->copy()->setTime(6, 30),
        ]);
    }
}
