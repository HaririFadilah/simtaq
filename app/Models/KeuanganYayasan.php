<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeuanganYayasan extends Model
{
    use HasFactory;

    protected $table = 'keuangan_yayasan';

    protected $fillable = [
        'kode_transaksi',
        'kategori_id',
        'jenis',
        'nominal',
        'saldo_berjalan',
        'tanggal',
        'keterangan',
        'bukti_nota',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'decimal:2',
            'saldo_berjalan' => 'decimal:2',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriTransaksi::class, 'kategori_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function alokasiKas(): HasMany
    {
        return $this->hasMany(KasOperasional::class, 'keuangan_yayasan_id');
    }
}
