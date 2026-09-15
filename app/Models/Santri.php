<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Santri extends Model
{
    use HasFactory;

    protected $table = 'santri';

    protected $fillable = [
        'nis',
        'nama_lengkap',
        'nama_panggilan',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'tanggal_masuk',
        'status',
        'wali_id',
        'asrama_id',
        'calon_santri_id',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_masuk' => 'date',
        ];
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(WaliSantri::class, 'wali_id');
    }

    public function asrama(): BelongsTo
    {
        return $this->belongsTo(Asrama::class, 'asrama_id');
    }

    public function calonSantri(): BelongsTo
    {
        return $this->belongsTo(CalonSantri::class, 'calon_santri_id');
    }

    public function perizinan(): HasMany
    {
        return $this->hasMany(PerizinanSantri::class, 'santri_id');
    }

    public function targetHafalan(): HasOne
    {
        return $this->hasOne(TargetHafalan::class, 'santri_id');
    }

    public function setoranHafalan(): HasMany
    {
        return $this->hasMany(SetoranHafalan::class, 'santri_id');
    }

    public function murojaah(): HasMany
    {
        return $this->hasMany(MurojaahSantri::class, 'santri_id');
    }

    public function evaluasiHafalan(): HasMany
    {
        return $this->hasMany(EvaluasiHafalan::class, 'santri_id');
    }

    public function mutabaah(): HasMany
    {
        return $this->hasMany(MutabaahSantri::class, 'santri_id');
    }
}
