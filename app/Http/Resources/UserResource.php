<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Data user yang login. Daftar izin ikut dikirim agar frontend bisa
 * menyusun menu dan menyembunyikan tombol yang tidak boleh dipakai.
 * Penjagaan sebenarnya tetap di server, lewat middleware route API.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'apotek' => $this->apotek,
            'jenis' => $this->isAdmin() ? 'admin' : ($this->isKasir() ? 'kasir' : null),
            'peran' => $this->getRoleNames()->values(),
            'izin' => $this->getAllPermissions()->pluck('name')->sort()->values(),
        ];
    }
}
