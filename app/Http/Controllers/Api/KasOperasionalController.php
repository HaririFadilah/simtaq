<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KasOperasional;
use App\Models\KategoriTransaksi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasOperasionalController extends Controller
{
    /**
     * List transaksi kas operasional (Putra / Putri) with role & scope enforcement.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Tentukan scope yang diizinkan
        $scope = null;
        if ($user->isKetuaSantri()) {
            $scope = $user->gender_scope; // 'putra' atau 'putri'
        } elseif ($user->isPengurus()) {
            $scope = $request->get('scope', 'putra'); // Pengurus bisa memilih putra atau putri
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat data kas operasional.',
            ], 403);
        }

        $query = KasOperasional::where('scope', $scope)->with(['kategori', 'user']);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun);
        }

        $transaksi = $query->latest('tanggal')->latest('id')->paginate($request->get('per_page', 15));

        $saldoTerkini = KasOperasional::where('scope', $scope)->latest('id')->first()?->saldo_berjalan ?? 0;
        $totalMasuk = KasOperasional::where('scope', $scope)->where('jenis', 'pemasukan')->sum('nominal');
        $totalKeluar = KasOperasional::where('scope', $scope)->where('jenis', 'pengeluaran')->sum('nominal');

        return response()->json([
            'success' => true,
            'data' => [
                'scope' => $scope,
                'label' => ($scope === 'putra') ? 'Kas Operasional Santri Putra' : 'Kas Operasional Santri Putri',
                'transaksi' => $transaksi,
                'ringkasan' => [
                    'saldo_terkini' => (float) $saldoTerkini,
                    'saldo_terkini_formatted' => 'Rp ' . number_format($saldoTerkini, 0, ',', '.'),
                    'total_masuk' => (float) $totalMasuk,
                    'total_masuk_formatted' => 'Rp ' . number_format($totalMasuk, 0, ',', '.'),
                    'total_keluar' => (float) $totalKeluar,
                    'total_keluar_formatted' => 'Rp ' . number_format($totalKeluar, 0, ',', '.'),
                ],
            ],
        ]);
    }

    /**
     * Store transaksi pengeluaran/pemasukan kas operasional.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validasi scope yang dikirim
        $scope = null;
        if ($user->isKetuaSantri()) {
            $scope = $user->gender_scope;
        } elseif ($user->isPengurus()) {
            $scope = $request->get('scope', 'putra');
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengelola kas operasional.',
            ], 403);
        }

        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_transaksi,id',
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'nominal' => 'required|numeric|min:500',
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
        ]);

        $transaksi = DB::transaction(function () use ($validated, $scope, $user) {
            $saldoTerakhir = KasOperasional::where('scope', $scope)->latest('id')->first()?->saldo_berjalan ?? 0;

            if ($validated['jenis'] === 'pengeluaran' && $saldoTerakhir < $validated['nominal']) {
                abort(422, 'Saldo kas operasional tidak mencukupi untuk pengeluaran ini.');
            }

            $saldoBaru = ($validated['jenis'] === 'pemasukan')
                ? $saldoTerakhir + $validated['nominal']
                : $saldoTerakhir - $validated['nominal'];

            $prefix = ($scope === 'putra') ? 'KAS-PA-' : 'KAS-PI-';
            $count = KasOperasional::where('scope', $scope)->count() + 1;
            $kode = $prefix . Carbon::now()->format('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $trans = KasOperasional::create([
                'kode_transaksi' => $kode,
                'scope' => $scope,
                'kategori_id' => $validated['kategori_id'],
                'jenis' => $validated['jenis'],
                'nominal' => $validated['nominal'],
                'saldo_berjalan' => $saldoBaru,
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'],
                'user_id' => $user->id,
            ]);

            ActivityLog::log(
                modul: ($scope === 'putra') ? 'Kas Putra' : 'Kas Putri',
                aksi: 'TRANSAKSI',
                judul: 'Transaksi Kas ' . ucfirst($scope) . ' (' . strtoupper($validated['jenis']) . ')',
                deskripsi: "Rp " . number_format($validated['nominal'], 0, ',', '.') . " - {$validated['keterangan']} oleh {$user->name}.",
                icon: 'payments',
                warna_badge: ($validated['jenis'] === 'pemasukan') ? 'positive' : 'negative'
            );

            return $trans;
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi kas operasional berhasil dicatat.',
            'data' => $transaksi->load('kategori'),
        ], 201);
    }

    /**
     * Kategori options for Kas Operasional.
     */
    public function getKategori(): JsonResponse
    {
        $kategori = KategoriTransaksi::whereIn('peruntukan', ['kas_putra', 'kas_putri', 'semua'])->get();

        return response()->json([
            'success' => true,
            'data' => $kategori,
        ]);
    }
}
