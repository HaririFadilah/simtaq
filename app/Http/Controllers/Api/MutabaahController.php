<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KegiatanMutabaah;
use App\Models\MutabaahSantri;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MutabaahController extends Controller
{
    /**
     * Get list of active master kegiatan mutabaah.
     */
    public function getMasterKegiatan(): JsonResponse
    {
        $kegiatan = KegiatanMutabaah::where('is_aktif', true)->orderBy('urutan')->get();

        return response()->json([
            'success' => true,
            'data' => $kegiatan,
        ]);
    }

    /**
     * Get daily checklist grid for a specific date and gender scope.
     */
    public function getDailySheet(Request $request): JsonResponse
    {
        $user = $request->user();
        $tanggal = $request->get('tanggal', Carbon::now()->toDateString());

        // Determine scope
        $gender = null;
        if ($user->isKetuaSantri()) {
            $gender = ($user->gender_scope === 'putra') ? 'L' : 'P';
        } elseif ($request->filled('gender')) {
            $gender = $request->gender;
        }

        $santriQuery = Santri::where('status', 'aktif')->with('asrama');
        if ($gender) {
            $santriQuery->where('jenis_kelamin', $gender);
        }

        if ($request->filled('asrama_id')) {
            $santriQuery->where('asrama_id', $request->asrama_id);
        }

        $santris = $santriQuery->orderBy('nama_lengkap')->get();
        $kegiatans = KegiatanMutabaah::where('is_aktif', true)->orderBy('urutan')->get();

        // Get existing entries for this date
        $existing = MutabaahSantri::whereDate('tanggal', $tanggal)
            ->whereIn('santri_id', $santris->pluck('id'))
            ->get()
            ->groupBy('santri_id');

        $rows = $santris->map(function ($snt) use ($kegiatans, $existing) {
            $santriEntries = $existing->get($snt->id, collect());
            $checklist = [];

            foreach ($kegiatans as $kgt) {
                $entry = $santriEntries->firstWhere('kegiatan_id', $kgt->id);
                $checklist[$kgt->id] = $entry ? $entry->status : 'hadir'; // default present
            }

            return [
                'santri_id' => $snt->id,
                'nis' => $snt->nis,
                'nama' => $snt->nama_lengkap,
                'asrama' => $snt->asrama?->nama_asrama ?? '-',
                'checklist' => $checklist,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $tanggal,
                'kegiatans' => $kegiatans,
                'sheet' => $rows,
            ],
        ]);
    }

    /**
     * Bulk save / update daily mutabaah checklist.
     */
    public function saveDailySheet(Request $request): JsonResponse
    {
        $request->validate([
            'tanggal' => 'required|date',
            'entries' => 'required|array',
            'entries.*.santri_id' => 'required|exists:santri,id',
            'entries.*.checklist' => 'required|array',
        ]);

        $user = $request->user();
        $tanggal = $request->tanggal;
        $entries = $request->entries;

        DB::transaction(function () use ($entries, $tanggal, $user) {
            foreach ($entries as $row) {
                $santriId = $row['santri_id'];
                foreach ($row['checklist'] as $kegiatanId => $status) {
                    MutabaahSantri::updateOrCreate(
                        [
                            'santri_id' => $santriId,
                            'kegiatan_id' => $kegiatanId,
                            'tanggal' => $tanggal,
                        ],
                        [
                            'ketua_santri_id' => $user->id,
                            'status' => in_array($status, ['hadir', 'tidak_hadir', 'izin', 'sakit']) ? $status : 'hadir',
                        ]
                    );
                }
            }
        });

        ActivityLog::log(
            modul: 'Mutabaah',
            aksi: 'CHECKLIST',
            judul: 'Checklist mutabaah yaumiyah disimpan',
            deskripsi: "Checklist mutabaah tanggal {$tanggal} untuk " . count($entries) . " santri berhasil dicatat oleh {$user->name}.",
            icon: 'checklist',
            warna_badge: 'positive'
        );

        return response()->json([
            'success' => true,
            'message' => 'Data mutabaah santri berhasil disimpan.',
        ]);
    }

    /**
     * Get trend statistics for charts.
     */
    public function getTrends(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        $kegiatans = KegiatanMutabaah::where('is_aktif', true)->get();

        $stats = $kegiatans->map(function ($kgt) use ($startDate) {
            $total = MutabaahSantri::where('kegiatan_id', $kgt->id)
                ->where('tanggal', '>=', $startDate)
                ->count();

            $hadir = MutabaahSantri::where('kegiatan_id', $kgt->id)
                ->where('tanggal', '>=', $startDate)
                ->where('status', 'hadir')
                ->count();

            $percentage = $total > 0 ? round(($hadir / $total) * 100, 1) : 95.0;

            return [
                'id' => $kgt->id,
                'nama' => $kgt->nama_kegiatan,
                'kode' => $kgt->kode,
                'persen' => $percentage,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
