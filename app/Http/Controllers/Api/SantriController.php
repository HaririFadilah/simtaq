<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Asrama;
use App\Models\Santri;
use App\Models\TargetHafalan;
use App\Models\WaliSantri;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SantriController extends Controller
{
    /**
     * List santri with comprehensive filters and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Santri::with(['asrama', 'wali', 'targetHafalan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        if ($request->filled('asrama_id')) {
            $query->where('asrama_id', $request->asrama_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nama_panggilan', 'like', "%{$search}%");
            });
        }

        $santri = $query->latest('id')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $santri,
        ]);
    }

    /**
     * Store new santri directly.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'nama_panggilan' => 'nullable|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'nis' => 'nullable|string|unique:santri,nis',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'tanggal_masuk' => 'required|date',
            'status' => 'required|in:aktif,keluar,lulus',
            'asrama_id' => 'nullable|exists:asrama,id',
            // Wali info
            'nama_wali' => 'nullable|string|max:150',
            'no_hp_wali' => 'nullable|string|max:25',
            'hubungan_wali' => 'nullable|in:ayah,ibu,wali',
            'alamat_wali' => 'nullable|string',
        ]);

        if (empty($validated['nis'])) {
            $year = Carbon::parse($validated['tanggal_masuk'])->format('Y');
            $countYear = Santri::whereYear('tanggal_masuk', $year)->count() + 1;
            $validated['nis'] = $year . str_pad($countYear, 4, '0', STR_PAD_LEFT);
        }

        $waliId = null;
        if (! empty($validated['nama_wali'])) {
            $wali = WaliSantri::create([
                'nama_wali' => $validated['nama_wali'],
                'no_hp' => $validated['no_hp_wali'] ?? null,
                'hubungan' => $validated['hubungan_wali'] ?? 'ayah',
                'alamat' => $validated['alamat_wali'] ?? $validated['alamat'],
            ]);
            $waliId = $wali->id;
        }

        $santri = Santri::create([
            'nis' => $validated['nis'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'nama_panggilan' => $validated['nama_panggilan'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'alamat' => $validated['alamat'],
            'tanggal_masuk' => $validated['tanggal_masuk'],
            'status' => $validated['status'],
            'asrama_id' => $validated['asrama_id'],
            'wali_id' => $waliId,
        ]);

        TargetHafalan::create([
            'santri_id' => $santri->id,
            'target_halaman_per_hari' => 1,
            'total_target_juz' => 30,
            'tanggal_mulai' => $santri->tanggal_masuk,
        ]);

        ActivityLog::log(
            modul: 'Santri',
            aksi: 'CREATE',
            judul: 'Penambahan data santri baru',
            deskripsi: "{$santri->nama_lengkap} (NIS: {$santri->nis}) berhasil ditambahkan.",
            icon: 'person',
            warna_badge: 'positive'
        );

        return response()->json([
            'success' => true,
            'message' => 'Data santri berhasil ditambahkan.',
            'data' => $santri->load('asrama', 'wali', 'targetHafalan'),
        ], 201);
    }

    /**
     * Show detail santri with relations.
     */
    public function show(Santri $santri): JsonResponse
    {
        $santri->load([
            'asrama',
            'wali',
            'targetHafalan',
            'setoranHafalan' => fn ($q) => $q->latest('tanggal')->take(10),
            'murojaah' => fn ($q) => $q->latest('tanggal_murojaah'),
            'evaluasiHafalan' => fn ($q) => $q->latest('tanggal_evaluasi'),
            'perizinan' => fn ($q) => $q->latest('waktu_mulai')->take(5),
        ]);

        $totalHalamanTuntas = $santri->setoranHafalan()->sum('total_halaman');
        $capaianJuz = round($totalHalamanTuntas / 20, 1);

        return response()->json([
            'success' => true,
            'data' => [
                'santri' => $santri,
                'ringkasan_hafalan' => [
                    'total_halaman' => $totalHalamanTuntas,
                    'capaian_juz' => $capaianJuz,
                    'target_juz' => $santri->targetHafalan?->total_target_juz ?? 30,
                    'persentase_selesai' => min(100, round(($capaianJuz / 30) * 100, 1)),
                ],
            ],
        ]);
    }

    /**
     * Update santri.
     */
    public function update(Request $request, Santri $santri): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'nama_panggilan' => 'nullable|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'tanggal_masuk' => 'required|date',
            'status' => 'required|in:aktif,keluar,lulus',
            'asrama_id' => 'nullable|exists:asrama,id',
        ]);

        $santri->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data santri berhasil diperbarui.',
            'data' => $santri->load('asrama', 'wali'),
        ]);
    }

    /**
     * List asrama options for dropdown.
     */
    public function listAsrama(): JsonResponse
    {
        $asrama = Asrama::withCount(['santri' => fn ($q) => $q->where('status', 'aktif')])->get();

        return response()->json([
            'success' => true,
            'data' => $asrama,
        ]);
    }
}
