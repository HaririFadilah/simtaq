<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CalonSantri extends Model
{
    use HasFactory;

    protected $table = 'calon_santri';

    protected $fillable = [
        'no_pendaftaran',
        'nama_lengkap',
        'nama_panggilan',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'asal_kota',
        'asal_sekolah',
        'nama_wali',
        'no_hp_wali',
        'alamat_wali',
        'tanggal_daftar',
        'status_seleksi',
        'ustadz_pewawancara_id',
        'hasil_wawancara',
        'catatan_ustadz',
        'is_converted',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_daftar' => 'date',
            'tanggal_lahir' => 'date',
            'is_converted' => 'boolean',
            'converted_at' => 'datetime',
        ];
    }

    public function pewawancara(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustadz_pewawancara_id');
    }

    public function santri(): HasOne
    {
        return $this->hasOne(Santri::class, 'calon_santri_id');
    }
}
