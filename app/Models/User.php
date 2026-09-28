<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'apotek',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Nama route dashboard sesuai role user.
     */
    public function dashboardRoute(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin.dashboard',
            $this->isKasir() => 'kasir.dashboard',
            default => 'login',
        };
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin_apotek_a', 'admin_apotek_b']);
    }

    public function isKasir(): bool
    {
        return $this->hasAnyRole(['kasir_apotek_a', 'kasir_apotek_b']);
    }

    public function hasApotek(string $apotek): bool
    {
        return $this->apotek === $apotek;
    }

    public function hasValidApotekRole(): bool
    {
        $roleApotek = match (true) {
            $this->hasAnyRole(['admin_apotek_a', 'kasir_apotek_a']) => 'Apotek A',
            $this->hasAnyRole(['admin_apotek_b', 'kasir_apotek_b']) => 'Apotek B',
            default => null,
        };

        return $roleApotek !== null && $this->apotek === $roleApotek;
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(Pembelian::class);
    }
}
