<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donasi extends Model
{
    use HasFactory;

    protected $table = 'donasi';

    protected $fillable = [
        'kode_donasi',
        'donatur_id',
        'nama_donatur_manual',
        'jenis_donasi',
        'nominal',
        'nama_barang',
        'jumlah_barang',
        'tanggal_donasi',
        'keterangan',
        'bukti_transaksi',
        'penerima_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_donasi' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public function donatur(): BelongsTo
    {
        return $this->belongsTo(Donatur::class, 'donatur_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function getNamaDonaturDisplayAttribute(): string
    {
        if ($this->donatur) {
            return $this->donatur->nama;
        }

        return $this->nama_donatur_manual ?? 'Hamba Allah';
    }
}
