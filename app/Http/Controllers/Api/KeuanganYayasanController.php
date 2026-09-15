<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KasOperasional;
use App\Models\KategoriTransaksi;
use App\Models\KeuanganYayasan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeuanganYayasanController extends Controller
{
    /**
     * List transaksi keuangan yayasan with filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = KeuanganYayasan::with(['kategori', 'user']);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun);
        }

        $transaksi = $query->latest('tanggal')->latest('id')->paginate($request->get('per_page', 15));

        $saldoTerkini = KeuanganYayasan::latest('id')->first()?->saldo_berjalan ?? 0;
        $totalMasuk = KeuanganYayasan::where('jenis', 'pemasukan')->sum('nominal');
        $totalKeluar = KeuanganYayasan::where('jenis', 'pengeluaran')->sum('nominal');

        return response()->json([
            'success' => true,
            'data' => [
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
     * Store transaksi kas yayasan (Pemasukan / Pengeluaran / Dropping Dana ke Asrama).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_transaksi,id',
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'nominal' => 'required|numeric|min:1000',
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
            // Jika transaksi adalah dropping dana ke kas asrama
            'is_alokasi_kas' => 'nullable|boolean',
            'alokasi_scope' => 'nullable|required_if:is_alokasi_kas,true|in:putra,putri',
        ]);

        $transaksi = DB::transaction(function () use ($validated, $request) {
            $saldoTerakhir = KeuanganYayasan::latest('id')->first()?->saldo_berjalan ?? 0;

            if ($validated['jenis'] === 'pengeluaran' && $saldoTerakhir < $validated['nominal']) {
                abort(422, 'Saldo kas yayasan tidak mencukupi untuk transaksi pengeluaran ini.');
            }

            $saldoBaru = ($validated['jenis'] === 'pemasukan')
                ? $saldoTerakhir + $validated['nominal']
                : $saldoTerakhir - $validated['nominal'];

            $kodeYys = 'YYS-' . Carbon::now()->format('Ym') . '-' . str_pad(KeuanganYayasan::count() + 1, 4, '0', STR_PAD_LEFT);

            $trans = KeuanganYayasan::create([
                'kode_transaksi' => $kodeYys,
                'kategori_id' => $validated['kategori_id'],
                'jenis' => $validated['jenis'],
                'nominal' => $validated['nominal'],
                'saldo_berjalan' => $saldoBaru,
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'],
                'user_id' => $request->user()->id,
            ]);

            // Jika alokasi dropping kas ke asrama
            if (! empty($validated['is_alokasi_kas']) && ! empty($validated['alokasi_scope'])) {
                $scope = $validated['alokasi_scope'];
                $saldoKasTerakhir = KasOperasional::where('scope', $scope)->latest('id')->first()?->saldo_berjalan ?? 0;
                $kategoriDroppingMasuk = KategoriTransaksi::where('nama_kategori', 'like', '%Dropping%')->first();

                $prefixScope = ($scope === 'putra') ? 'KAS-PA-' : 'KAS-PI-';
                $countKasScope = KasOperasional::where('scope', $scope)->count() + 1;
                $kodeKas = $prefixScope . Carbon::now()->format('Ym') . '-' . str_pad($countKasScope, 4, '0', STR_PAD_LEFT);

                KasOperasional::create([
                    'kode_transaksi' => $kodeKas,
                    'scope' => $scope,
                    'kategori_id' => $kategoriDroppingMasuk?->id,
                    'jenis' => 'pemasukan',
                    'nominal' => $validated['nominal'],
                    'saldo_berjalan' => $saldoKasTerakhir + $validated['nominal'],
                    'tanggal' => $validated['tanggal'],
                    'keterangan' => 'Dropping Dana dari Yayasan: ' . $validated['keterangan'],
                    'user_id' => $request->user()->id,
                    'keuangan_yayasan_id' => $trans->id,
                ]);
            }

            ActivityLog::log(
                modul: 'Keuangan',
                aksi: 'TRANSAKSI',
                judul: 'Transaksi Keuangan Yayasan (' . strtoupper($validated['jenis']) . ')',
                deskripsi: "Nominal: Rp " . number_format($validated['nominal'], 0, ',', '.') . " - {$validated['keterangan']}",
                icon: 'account_balance_wallet',
                warna_badge: ($validated['jenis'] === 'pemasukan') ? 'positive' : 'negative'
            );

            return $trans;
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi keuangan yayasan berhasil dicatat.',
            'data' => $transaksi->load('kategori'),
        ], 201);
    }

    /**
     * Get master kategori transaksi for yayasan.
     */
    public function getKategori(): JsonResponse
    {
        $kategori = KategoriTransaksi::whereIn('peruntukan', ['yayasan', 'semua'])->get();

        return response()->json([
            'success' => true,
            'data' => $kategori,
        ]);
    }
}
