<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_approved',
        'approved_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'is_approved' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function memberFiles(): HasMany
    {
        return $this->hasMany(MemberFile::class);
    }

    public function memberFolders(): HasMany
    {
        return $this->hasMany(MemberFolder::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
