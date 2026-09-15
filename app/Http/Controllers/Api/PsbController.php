<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CalonSantri;
use App\Models\Santri;
use App\Models\TargetHafalan;
use App\Models\WaliSantri;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsbController extends Controller
{
    /**
     * List all calon santri with search & filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CalonSantri::with('pewawancara');

        if ($request->filled('status')) {
            $query->where('status_seleksi', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('no_pendaftaran', 'like', "%{$search}%")
                  ->orWhere('asal_kota', 'like', "%{$search}%");
            });
        }

        $calonSantri = $query->latest('tanggal_daftar')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $calonSantri,
        ]);
    }

    /**
     * Store new calon santri registration.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'nama_panggilan' => 'nullable|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'asal_kota' => 'nullable|string|max:100',
            'asal_sekolah' => 'nullable|string|max:150',
            'nama_wali' => 'required|string|max:150',
            'no_hp_wali' => 'required|string|max:25',
            'alamat_wali' => 'nullable|string',
            'status_seleksi' => 'nullable|in:diproses,wawancara,diterima,ditolak,cadangan',
            'hasil_wawancara' => 'nullable|string',
            'catatan_ustadz' => 'nullable|string',
        ]);

        $year = Carbon::now()->format('Y');
        $countThisYear = CalonSantri::whereYear('created_at', $year)->count() + 1;
        $validated['no_pendaftaran'] = 'PSB-' . $year . '-' . str_pad($countThisYear, 3, '0', STR_PAD_LEFT);
        $validated['tanggal_daftar'] = Carbon::now()->toDateString();
        $validated['status_seleksi'] = $validated['status_seleksi'] ?? 'diproses';
        $validated['ustadz_pewawancara_id'] = $request->user()->id;

        $calon = CalonSantri::create($validated);

        ActivityLog::log(
            modul: 'PSB',
            aksi: 'CREATE',
            judul: 'Pendaftaran calon santri baru',
            deskripsi: "{$calon->nama_lengkap} ({$calon->no_pendaftaran}) telah didaftarkan.",
            icon: 'person_add',
            warna_badge: 'positive'
        );

        return response()->json([
            'success' => true,
            'message' => 'Data calon santri berhasil didaftarkan.',
            'data' => $calon,
        ], 201);
    }

    /**
     * Show calon santri detail.
     */
    public function show(CalonSantri $calonSantri): JsonResponse
    {
        $calonSantri->load('pewawancara', 'santri');

        return response()->json([
            'success' => true,
            'data' => $calonSantri,
        ]);
    }

    /**
     * Update calon santri / hasil wawancara.
     */
    public function update(Request $request, CalonSantri $calonSantri): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'nama_panggilan' => 'nullable|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'asal_kota' => 'nullable|string|max:100',
            'asal_sekolah' => 'nullable|string|max:150',
            'nama_wali' => 'required|string|max:150',
            'no_hp_wali' => 'required|string|max:25',
            'alamat_wali' => 'nullable|string',
            'status_seleksi' => 'required|in:diproses,wawancara,diterima,ditolak,cadangan',
            'hasil_wawancara' => 'nullable|string',
            'catatan_ustadz' => 'nullable|string',
        ]);

        if (! empty($validated['hasil_wawancara']) && empty($calonSantri->ustadz_pewawancara_id)) {
            $validated['ustadz_pewawancara_id'] = $request->user()->id;
        }

        $calonSantri->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data calon santri berhasil diperbarui.',
            'data' => $calonSantri,
        ]);
    }

    /**
     * Konversi Calon Santri Diterima menjadi Santri Baru (1-Klik Tanpa Input Ulang).
     */
    public function convertToSantri(Request $request, CalonSantri $calonSantri): JsonResponse
    {
        if ($calonSantri->is_converted) {
            return response()->json([
                'success' => false,
                'message' => 'Calon santri ini sudah pernah dikonversi menjadi santri aktif.',
            ], 422);
        }

        if ($calonSantri->status_seleksi !== 'diterima') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya calon santri berstatus [Diterima] yang dapat dikonversi menjadi santri baru.',
            ], 422);
        }

        $request->validate([
            'asrama_id' => 'nullable|exists:asrama,id',
            'nis' => 'nullable|string|unique:santri,nis',
        ]);

        $santri = DB::transaction(function () use ($calonSantri, $request) {
            // 1. Buat data wali jika belum ada
            $wali = WaliSantri::create([
                'nama_wali' => $calonSantri->nama_wali,
                'no_hp' => $calonSantri->no_hp_wali,
                'alamat' => $calonSantri->alamat_wali,
                'hubungan' => 'ayah',
            ]);

            // 2. Generate NIS unik jika tidak diberikan manual
            $year = Carbon::now()->format('Y');
            $nis = $request->nis;
            if (! $nis) {
                $countSantriYear = Santri::whereYear('tanggal_masuk', $year)->count() + 1;
                $nis = $year . str_pad($countSantriYear, 4, '0', STR_PAD_LEFT);
            }

            // 3. Buat data Santri
            $newSantri = Santri::create([
                'nis' => $nis,
                'nama_lengkap' => $calonSantri->nama_lengkap,
                'nama_panggilan' => $calonSantri->nama_panggilan,
                'jenis_kelamin' => $calonSantri->jenis_kelamin,
                'tempat_lahir' => $calonSantri->tempat_lahir,
                'tanggal_lahir' => $calonSantri->tanggal_lahir,
                'alamat' => $calonSantri->alamat_wali,
                'tanggal_masuk' => Carbon::now()->toDateString(),
                'status' => 'aktif',
                'wali_id' => $wali->id,
                'asrama_id' => $request->asrama_id,
                'calon_santri_id' => $calonSantri->id,
            ]);

            // 4. Inisialisasi target hafalan awal
            TargetHafalan::create([
                'santri_id' => $newSantri->id,
                'target_halaman_per_hari' => 1,
                'total_target_juz' => 30,
                'tanggal_mulai' => $newSantri->tanggal_masuk,
            ]);

            // 5. Tandai Calon Santri sudah dikonversi
            $calonSantri->update([
                'is_converted' => true,
                'converted_at' => Carbon::now(),
            ]);

            ActivityLog::log(
                modul: 'PSB',
                aksi: 'KONVERSI',
                judul: 'Santri baru diterima & dikonversi',
                deskripsi: "{$newSantri->nama_lengkap} (NIS: {$newSantri->nis}) telah resmi menjadi santri aktif.",
                icon: 'how_to_reg',
                warna_badge: 'positive'
            );

            return $newSantri;
        });

        return response()->json([
            'success' => true,
            'message' => "Selamat! {$santri->nama_lengkap} berhasil dikonversi menjadi santri aktif dengan NIS {$santri->nis}.",
            'data' => $santri->load('wali', 'asrama'),
        ]);
    }

    /**
     * Public self-registration for calon santri (accessible without authentication).
     */
    public function registerPublic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'nama_panggilan' => 'nullable|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'asal_kota' => 'nullable|string|max:100',
            'asal_sekolah' => 'nullable|string|max:150',
            'nama_wali' => 'required|string|max:150',
            'no_hp_wali' => 'required|string|max:25',
            'alamat_wali' => 'nullable|string',
            'catatan_wali' => 'nullable|string|max:500',
        ]);

        $year = Carbon::now()->format('Y');
        $countThisYear = CalonSantri::whereYear('created_at', $year)->count() + 1;
        $validated['no_pendaftaran'] = 'PSB-' . $year . '-' . str_pad($countThisYear, 3, '0', STR_PAD_LEFT);
        $validated['tanggal_daftar'] = Carbon::now()->toDateString();
        $validated['status_seleksi'] = 'diproses';
        $validated['ustadz_pewawancara_id'] = null;

        if (!empty($validated['catatan_wali'])) {
            $validated['catatan_ustadz'] = '[Catatan Wali]: ' . $validated['catatan_wali'];
            unset($validated['catatan_wali']);
        }

        $calon = CalonSantri::create($validated);

        ActivityLog::log(
            modul: 'PSB',
            aksi: 'CREATE',
            judul: 'Pendaftaran Mandiri PSB Online',
            deskripsi: "{$calon->nama_lengkap} ({$calon->no_pendaftaran}) mendaftar secara online.",
            icon: 'how_to_reg',
            warna_badge: 'info'
        );

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran online calon santri berhasil! Simpan nomor pendaftaran Anda.',
            'data' => [
                'no_pendaftaran' => $calon->no_pendaftaran,
                'nama_lengkap' => $calon->nama_lengkap,
                'jenis_kelamin' => $calon->jenis_kelamin,
                'tanggal_daftar' => $calon->tanggal_daftar,
                'nama_wali' => $calon->nama_wali,
                'no_hp_wali' => $calon->no_hp_wali,
                'status_seleksi' => $calon->status_seleksi,
            ],
        ], 201);
    }

    /**
     * Public check registration status using no_pendaftaran.
     */
    public function checkPublicStatus(string $no_pendaftaran): JsonResponse
    {
        $calon = CalonSantri::where('no_pendaftaran', trim($no_pendaftaran))->first();

        if (!$calon) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor pendaftaran tidak ditemukan. Periksa kembali nomor yang Anda masukkan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'no_pendaftaran' => $calon->no_pendaftaran,
                'nama_lengkap' => $calon->nama_lengkap,
                'jenis_kelamin' => $calon->jenis_kelamin,
                'asal_kota' => $calon->asal_kota,
                'tanggal_daftar' => $calon->tanggal_daftar,
                'status_seleksi' => $calon->status_seleksi,
                'is_converted' => $calon->is_converted,
                'catatan' => $calon->status_seleksi === 'diterima'
                    ? 'Selamat! Anda dinyatakan DITERIMA di SIMTAQ Yayasan Al Mukhlisin. Silakan hubungi admin pondok untuk registrasi ulang.'
                    : ($calon->status_seleksi === 'diproses'
                        ? 'Berkas pendaftaran Anda sedang dalam tahap verifikasi panitia seleksi.'
                        : 'Status: ' . ucfirst($calon->status_seleksi)),
            ],
        ]);
    }
}
