<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MurojaahSantri extends Model
{
    use HasFactory;

    protected $table = 'murojaah_santri';

    protected $fillable = [
        'santri_id',
        'ustadz_id',
        'juz',
        'tanggal_murojaah',
        'status',
        'nilai',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_murojaah' => 'date',
            'juz' => 'integer',
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
