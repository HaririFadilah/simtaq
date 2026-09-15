<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donatur extends Model
{
    use HasFactory;

    protected $table = 'donatur';

    protected $fillable = [
        'nama',
        'tipe_donatur',
        'no_hp',
        'email',
        'alamat',
        'kategori',
    ];

    public function donasi(): HasMany
    {
        return $this->hasMany(Donasi::class, 'donatur_id');
    }
}
