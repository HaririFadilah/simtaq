<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiHafalan extends Model
{
    use HasFactory;

    protected $table = 'evaluasi_hafalan';

    protected $fillable = [
        'santri_id',
        'penguji_id',
        'kelipatan_juz',
        'tanggal_evaluasi',
        'nilai_kelancaran',
        'nilai_tajwid',
        'nilai_makhraj',
        'nilai_total',
        'status_kelulusan',
        'no_sertifikat',
        'catatan_penguji',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_evaluasi' => 'date',
            'kelipatan_juz' => 'integer',
            'nilai_kelancaran' => 'decimal:2',
            'nilai_tajwid' => 'decimal:2',
            'nilai_makhraj' => 'decimal:2',
            'nilai_total' => 'decimal:2',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function penguji(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penguji_id');
    }
}
