<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetoranHafalan extends Model
{
    use HasFactory;

    protected $table = 'setoran_hafalan';

    protected $fillable = [
        'santri_id',
        'ustadz_id',
        'tanggal',
        'juz',
        'halaman_mulai',
        'halaman_selesai',
        'total_halaman',
        'surat_ayat_info',
        'jenis_setoran',
        'kualitas',
        'nilai',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'juz' => 'integer',
            'halaman_mulai' => 'integer',
            'halaman_selesai' => 'integer',
            'total_halaman' => 'integer',
            'nilai' => 'integer',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustadz_id');
    }
}
