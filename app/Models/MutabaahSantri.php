<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutabaahSantri extends Model
{
    use HasFactory;

    protected $table = 'mutabaah_santri';

    protected $fillable = [
        'santri_id',
        'kegiatan_id',
        'ketua_santri_id',
        'tanggal',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(KegiatanMutabaah::class, 'kegiatan_id');
    }

    public function ketuaSantri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_santri_id');
    }
}
