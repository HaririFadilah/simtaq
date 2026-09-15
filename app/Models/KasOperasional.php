<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasOperasional extends Model
{
    use HasFactory;

    protected $table = 'kas_operasional';

    protected $fillable = [
        'kode_transaksi',
        'scope',
        'kategori_id',
        'jenis',
        'nominal',
        'saldo_berjalan',
        'tanggal',
        'keterangan',
        'bukti_nota',
        'user_id',
        'keuangan_yayasan_id',
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

    public function transferYayasan(): BelongsTo
    {
        return $this->belongsTo(KeuanganYayasan::class, 'keuangan_yayasan_id');
    }

    public function scopePutra(Builder $query): Builder
    {
        return $query->where('scope', 'putra');
    }

    public function scopePutri(Builder $query): Builder
    {
        return $query->where('scope', 'putri');
    }
}
