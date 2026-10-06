<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Aturan masuk yang dipakai bersama halaman login dan API:
 * batas percobaan, cek password, dan cek role apotek.
 */
class MasukkanUser
{
    /**
     * @param  array{email: string, password: string}  $kredensial
     *
     * @throws ValidationException
     */
    public function __invoke(array $kredensial, bool $ingat, string $ip): User
    {
        $kunciBatas = Str::transliterate(Str::lower($kredensial['email']).'|'.$ip);

        if (RateLimiter::tooManyAttempts($kunciBatas, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($kunciBatas).' detik.',
            ]);
        }

        if (! Auth::attempt($kredensial, $ingat)) {
            RateLimiter::hit($kunciBatas);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($kunciBatas);

        /** @var User $user */
        $user = Auth::user();

        // User tanpa role tidak punya halaman tujuan
        if (! $user->hasValidApotekRole()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda belum memiliki hak akses. Hubungi administrator.',
            ]);
        }

        return $user;
    }
}
