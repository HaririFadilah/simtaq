<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetHafalan extends Model
{
    use HasFactory;

    protected $table = 'target_hafalan';

    protected $fillable = [
        'santri_id',
        'target_halaman_per_hari',
        'total_target_juz',
        'tanggal_mulai',
        'target_selesai',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'target_selesai' => 'date',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
