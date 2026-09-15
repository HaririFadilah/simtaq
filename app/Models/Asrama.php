<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asrama extends Model
{
    use HasFactory;

    protected $table = 'asrama';

    protected $fillable = [
        'nama_asrama',
        'gender',
        'kapasitas',
        'keterangan',
    ];

    public function santri(): HasMany
    {
        return $this->hasMany(Santri::class, 'asrama_id');
    }
}
