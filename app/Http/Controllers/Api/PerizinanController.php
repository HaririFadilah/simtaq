<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PerizinanSantri;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerizinanController extends Controller
{
    /**
     * List all perizinan santri with status filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PerizinanSantri::with(['santri.asrama', 'ustadz', 'ustadzKonfirmasi']);

        // Check and auto-flag overdue perizinan in query
        if ($request->filled('status')) {
            if ($request->status === 'terlambat') {
                $query->where('status', 'sedang_izin')
                      ->where('batas_kembali', '<', Carbon::now());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $perizinan = $query->latest('waktu_mulai')->paginate($request->get('per_page', 15));

        // Format overdue badge dynamically
        $now = Carbon::now();
        $perizinan->getCollection()->transform(function ($item) use ($now) {
            $isOverdue = ($item->status === 'sedang_izin' && $now->isAfter($item->batas_kembali));
            $item->is_overdue = $isOverdue;
            if ($isOverdue && $item->status === 'sedang_izin') {
                $item->status_display = 'terlambat';
            } else {
                $item->status_display = $item->status;
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $perizinan,
        ]);
    }

    /**
     * Store new perizinan (oleh Ustadz langsung).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'santri_id' => 'required|exists:santri,id',
            'jenis_izin' => 'required|in:pulang,sakit,keperluan_mendesak,kegiatan_luar,lainnya',
            'alasan' => 'required|string',
            'waktu_mulai' => 'required|date',
            'batas_kembali' => 'required|date|after:waktu_mulai',
        ]);

        $validated['ustadz_id'] = $request->user()->id;
        $validated['status'] = 'sedang_izin';

        $izin = PerizinanSantri::create($validated);
        $izin->load('santri', 'ustadz');

        ActivityLog::log(
            modul: 'Perizinan',
            aksi: 'CREATE',
            judul: 'Izin santri diterbitkan',
            deskripsi: "{$izin->santri->nama_lengkap} diberikan izin {$izin->jenis_izin} oleh {$izin->ustadz->name}.",
            icon: 'assignment_turned_in',
            warna_badge: 'warning'
        );

        return response()->json([
            'success' => true,
            'message' => 'Izin santri berhasil dicatat.',
            'data' => $izin,
        ], 201);
    }

    /**
     * Konfirmasi santri telah kembali ke pondok.
     */
    public function konfirmasiKembali(Request $request, PerizinanSantri $perizinanSantri): JsonResponse
    {
        if ($perizinanSantri->status === 'telah_kembali') {
            return response()->json([
                'success' => false,
                'message' => 'Perizinan ini sudah pernah dikonfirmasi kembali sebelumnya.',
            ], 422);
        }

        $request->validate([
            'catatan_kembali' => 'nullable|string',
        ]);

        $now = Carbon::now();
        $isLate = $now->isAfter($perizinanSantri->batas_kembali);

        $perizinanSantri->update([
            'waktu_kembali' => $now,
            'status' => 'telah_kembali',
            'ustadz_konfirmasi_id' => $request->user()->id,
            'catatan_kembali' => $request->catatan_kembali . ($isLate ? ' (Terlambat kembali)' : ''),
        ]);

        ActivityLog::log(
            modul: 'Perizinan',
            aksi: 'KONFIRMASI',
            judul: 'Izin santri dikonfirmasi kembali',
            deskripsi: "{$perizinanSantri->santri->nama_lengkap} telah kembali ke pondok (Status: " . ($isLate ? 'Terlambat' : 'Tepat Waktu') . ").",
            icon: 'person_pin_circle',
            warna_badge: $isLate ? 'negative' : 'positive'
        );

        return response()->json([
            'success' => true,
            'message' => 'Kedatangan santri berhasil dikonfirmasi.',
            'data' => $perizinanSantri->load('santri', 'ustadzKonfirmasi'),
        ]);
    }
}
