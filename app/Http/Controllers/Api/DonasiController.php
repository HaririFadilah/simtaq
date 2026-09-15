<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Donasi;
use App\Models\KategoriTransaksi;
use App\Models\KeuanganYayasan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonasiController extends Controller
{
    /**
     * List donasi with filter by jenis, date, search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Donasi::with(['donatur', 'penerima']);

        if ($request->filled('jenis_donasi')) {
            $query->where('jenis_donasi', $request->jenis_donasi);
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('tanggal_donasi', $request->bulan)
                  ->whereYear('tanggal_donasi', $request->tahun);
        }

        $donasi = $query->latest('tanggal_donasi')->latest('id')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $donasi,
        ]);
    }

    /**
     * Store new donasi (uang, makanan, barang).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'donatur_id' => 'nullable|exists:donatur,id',
            'nama_donatur_manual' => 'nullable|string|max:150',
            'jenis_donasi' => 'required|in:uang,makanan,barang',
            'nominal' => 'nullable|required_if:jenis_donasi,uang|numeric|min:1000',
            'nama_barang' => 'nullable|required_if:jenis_donasi,makanan,barang|string|max:150',
            'jumlah_barang' => 'nullable|required_if:jenis_donasi,makanan,barang|string|max:100',
            'tanggal_donasi' => 'required|date',
            'keterangan' => 'nullable|string',
            'masuk_ke_kas_yayasan' => 'nullable|boolean',
        ]);

        $year = Carbon::parse($validated['tanggal_donasi'])->format('Y');
        $countYear = Donasi::whereYear('tanggal_donasi', $year)->count() + 1;
        $validated['kode_donasi'] = 'DNS-' . $year . '-' . str_pad($countYear, 4, '0', STR_PAD_LEFT);
        $validated['penerima_id'] = $request->user()->id;

        $donasi = DB::transaction(function () use ($validated, $request) {
            $record = Donasi::create($validated);

            // Jika jenis donasi adalah uang dan dicentang masuk ke kas yayasan (default true)
            if ($record->jenis_donasi === 'uang' && ($request->get('masuk_ke_kas_yayasan', true))) {
                $saldoTerakhir = KeuanganYayasan::latest('id')->first()?->saldo_berjalan ?? 0;
                $kategoriInfaq = KategoriTransaksi::where('nama_kategori', 'like', '%Infaq%')->first();

                KeuanganYayasan::create([
                    'kode_transaksi' => 'YYS-' . Carbon::now()->format('Ym') . '-' . str_pad(KeuanganYayasan::count() + 1, 3, '0', STR_PAD_LEFT),
                    'kategori_id' => $kategoriInfaq?->id,
                    'jenis' => 'pemasukan',
                    'nominal' => $record->nominal,
                    'saldo_berjalan' => $saldoTerakhir + $record->nominal,
                    'tanggal' => $record->tanggal_donasi,
                    'keterangan' => 'Donasi Uang (' . $record->nama_donatur_display . '): ' . ($record->keterangan ?? '-'),
                    'user_id' => $request->user()->id,
                ]);
            }

            ActivityLog::log(
                modul: 'Donasi',
                aksi: 'CREATE',
                judul: 'Donasi baru diterima',
                deskripsi: ($record->jenis_donasi === 'uang')
                    ? "{$record->nama_donatur_display} mendonasikan Rp " . number_format($record->nominal, 0, ',', '.')
                    : "{$record->nama_donatur_display} mendonasikan {$record->jumlah_barang} {$record->nama_barang}.",
                icon: 'favorite',
                warna_badge: 'negative'
            );

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pencatatan donasi berhasil disimpan.',
            'data' => $donasi->load('donatur', 'penerima'),
        ], 201);
    }
}
