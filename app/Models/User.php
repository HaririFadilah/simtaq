<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'gender_scope',
        'no_hp',
        'foto',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isPengurus(): bool
    {
        return $this->role === 'pengurus';
    }

    public function isUstadz(): bool
    {
        return $this->role === 'ustadz';
    }

    public function isKetuaSantri(): bool
    {
        return $this->role === 'ketua_santri';
    }

    public function canAccessScope(string $scope): bool
    {
        if ($this->isPengurus()) {
            return true;
        }

        if ($this->gender_scope === 'semua') {
            return true;
        }

        return $this->gender_scope === $scope;
    }
}
