<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\EvaluasiHafalan;
use App\Models\MurojaahSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use App\Models\TargetHafalan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HafalanController extends Controller
{
    /**
     * List recent setoran hafalan with filter.
     */
    public function indexSetoran(Request $request): JsonResponse
    {
        $query = SetoranHafalan::with(['santri.asrama', 'ustadz']);

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }

        if ($request->filled('juz')) {
            $query->where('juz', $request->juz);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $setoran = $query->latest('tanggal')->latest('id')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $setoran,
        ]);
    }

    /**
     * Store new setoran hafalan (Ziyadah / Muroja'ah Harian / Tasmi').
     */
    public function storeSetoran(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'santri_id' => 'required|exists:santri,id',
            'tanggal' => 'required|date',
            'juz' => 'required|integer|min:1|max:30',
            'halaman_mulai' => 'required|integer|min:1|max:604',
            'halaman_selesai' => 'required|integer|min:1|max:604|gte:halaman_mulai',
            'surat_ayat_info' => 'nullable|string|max:150',
            'jenis_setoran' => 'required|in:ziyadah,murojaah_harian,tasmi',
            'kualitas' => 'required|in:mutqin,jayyid,maqbul,ulang',
            'nilai' => 'required|integer|min:0|max:100',
            'catatan' => 'nullable|string',
        ]);

        $validated['total_halaman'] = ($validated['halaman_selesai'] - $validated['halaman_mulai']) + 1;
        $validated['ustadz_id'] = $request->user()->id;

        $setoran = SetoranHafalan::create($validated);
        $santri = Santri::find($validated['santri_id']);

        // Check if completing 1 Juz (trigger Muroja'ah)
        $totalHalamanJuz = SetoranHafalan::where('santri_id', $santri->id)
            ->where('juz', $validated['juz'])
            ->sum('total_halaman');

        $triggerMurojaah = false;
        if ($totalHalamanJuz >= 20) {
            $alreadyMurojaah = MurojaahSantri::where('santri_id', $santri->id)
                ->where('juz', $validated['juz'])
                ->exists();

            if (! $alreadyMurojaah) {
                $triggerMurojaah = true;
            }
        }

        ActivityLog::log(
            modul: 'Hafalan',
            aksi: 'SETORAN',
            judul: 'Setoran hafalan santri',
            deskripsi: "{$santri->nama_lengkap} menyetorkan Juz {$validated['juz']} ({$validated['total_halaman']} hal). Nilai: {$validated['nilai']}.",
            icon: 'menu_book',
            warna_badge: 'warning'
        );

        return response()->json([
            'success' => true,
            'message' => 'Setoran hafalan berhasil dicatat.',
            'data' => [
                'setoran' => $setoran->load('santri', 'ustadz'),
                'trigger_murojaah' => $triggerMurojaah,
                'juz_selesai' => $triggerMurojaah ? $validated['juz'] : null,
            ],
        ], 201);
    }

    /**
     * List and store Muroja'ah Tuntas 1 Juz.
     */
    public function indexMurojaah(Request $request): JsonResponse
    {
        $query = MurojaahSantri::with(['santri', 'ustadz']);

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }

        $murojaah = $query->latest('tanggal_murojaah')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $murojaah,
        ]);
    }

    public function storeMurojaah(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'santri_id' => 'required|exists:santri,id',
            'juz' => 'required|integer|min:1|max:30',
            'tanggal_murojaah' => 'required|date',
            'status' => 'required|in:tuntas,ulang',
            'nilai' => 'required|integer|min:0|max:100',
            'catatan' => 'nullable|string',
        ]);

        $validated['ustadz_id'] = $request->user()->id;

        $murojaah = MurojaahSantri::create($validated);
        $santri = Santri::find($validated['santri_id']);

        // Check if reached multiple of 5 Juz (5, 10, 15, 20, 25, 30)
        $totalJuzTuntas = MurojaahSantri::where('santri_id', $santri->id)
            ->where('status', 'tuntas')
            ->distinct('juz')
            ->count('juz');

        $triggerEvaluasi5Juz = false;
        $kelipatan = null;
        if ($totalJuzTuntas >= 5 && ($totalJuzTuntas % 5 === 0)) {
            $kelipatan = $totalJuzTuntas;
            $sudahEvaluasi = EvaluasiHafalan::where('santri_id', $santri->id)
                ->where('kelipatan_juz', $kelipatan)
                ->exists();

            if (! $sudahEvaluasi) {
                $triggerEvaluasi5Juz = true;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Catatan murojaah juz berhasil disimpan.',
            'data' => [
                'murojaah' => $murojaah,
                'trigger_evaluasi_5_juz' => $triggerEvaluasi5Juz,
                'kelipatan_juz' => $kelipatan,
            ],
        ], 201);
    }

    /**
     * Evaluasi Akbar Kelipatan 5 Juz (5, 10, 15, 20, 25, 30).
     */
    public function indexEvaluasi(Request $request): JsonResponse
    {
        $query = EvaluasiHafalan::with(['santri', 'penguji']);

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }

        $evaluasi = $query->latest('tanggal_evaluasi')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $evaluasi,
        ]);
    }

    public function storeEvaluasi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'santri_id' => 'required|exists:santri,id',
            'kelipatan_juz' => 'required|in:5,10,15,20,25,30',
            'tanggal_evaluasi' => 'required|date',
            'nilai_kelancaran' => 'required|numeric|min:0|max:100',
            'nilai_tajwid' => 'required|numeric|min:0|max:100',
            'nilai_makhraj' => 'required|numeric|min:0|max:100',
            'status_kelulusan' => 'required|in:lulus_mumtaz,lulus_jayyid,mengulang',
            'catatan_penguji' => 'nullable|string',
        ]);

        $validated['penguji_id'] = $request->user()->id;
        $validated['nilai_total'] = round(($validated['nilai_kelancaran'] * 0.4) + ($validated['nilai_tajwid'] * 0.3) + ($validated['nilai_makhraj'] * 0.3), 2);

        if ($validated['status_kelulusan'] !== 'mengulang') {
            $year = Carbon::now()->format('Y');
            $validated['no_sertifikat'] = 'SRT-TAF/' . $year . '/' . $validated['kelipatan_juz'] . 'JUZ/' . str_pad($validated['santri_id'], 4, '0', STR_PAD_LEFT);
        }

        $evaluasi = EvaluasiHafalan::create($validated);
        $santri = Santri::find($validated['santri_id']);

        ActivityLog::log(
            modul: 'Hafalan',
            aksi: 'UJIAN',
            judul: 'Ujian evaluasi hafalan kelipatan 5 juz',
            deskripsi: "{$santri->nama_lengkap} telah menyelesaikan evaluasi {$validated['kelipatan_juz']} Juz. Hasil: {$validated['status_kelulusan']}.",
            icon: 'workspace_premium',
            warna_badge: 'positive'
        );

        return response()->json([
            'success' => true,
            'message' => 'Hasil evaluasi kelipatan 5 juz berhasil dicatat.',
            'data' => $evaluasi->load('santri', 'penguji'),
        ], 201);
    }
}
