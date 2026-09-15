<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KegiatanMutabaah extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_mutabaah';

    protected $fillable = [
        'nama_kegiatan',
        'kode',
        'kategori',
        'urutan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function mutabaahSantri(): HasMany
    {
        return $this->hasMany(MutabaahSantri::class, 'kegiatan_id');
    }
}
