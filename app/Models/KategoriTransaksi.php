<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kategori_transaksi';

    protected $fillable = [
        'nama_kategori',
        'jenis',
        'peruntukan',
    ];

    public function keuanganYayasan(): HasMany
    {
        return $this->hasMany(KeuanganYayasan::class, 'kategori_id');
    }

    public function kasOperasional(): HasMany
    {
        return $this->hasMany(KasOperasional::class, 'kategori_id');
    }
}
