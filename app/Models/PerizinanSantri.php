<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerizinanSantri extends Model
{
    use HasFactory;

    protected $table = 'perizinan_santri';

    protected $fillable = [
        'santri_id',
        'ustadz_id',
        'jenis_izin',
        'alasan',
        'waktu_mulai',
        'batas_kembali',
        'waktu_kembali',
        'status',
        'ustadz_konfirmasi_id',
        'catatan_kembali',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'batas_kembali' => 'datetime',
            'waktu_kembali' => 'datetime',
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

    public function ustadzKonfirmasi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustadz_konfirmasi_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'telah_kembali') {
            return false;
        }

        return Carbon::now()->isAfter($this->batas_kembali);
    }
}
