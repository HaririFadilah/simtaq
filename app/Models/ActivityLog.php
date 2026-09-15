<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'modul',
        'aksi',
        'judul',
        'deskripsi',
        'icon',
        'warna_badge',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log(
        string $modul,
        string $aksi,
        string $judul,
        string $deskripsi,
        string $icon = 'check_circle',
        string $warna_badge = 'positive',
        ?array $payload = null,
        ?int $userId = null
    ): self {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'modul' => $modul,
            'aksi' => $aksi,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'icon' => $icon,
            'warna_badge' => $warna_badge,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payload' => $payload,
        ]);
    }
}
