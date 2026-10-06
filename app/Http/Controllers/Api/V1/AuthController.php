<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\MasukkanUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Masuk dan keluar untuk frontend Next.js.
 *
 * Memakai cookie session (Sanctum SPA), bukan token: frontend lebih dulu
 * memanggil GET /sanctum/csrf-cookie, lalu mengirim header X-XSRF-TOKEN.
 */
class AuthController extends Controller
{
    public function login(Request $request, MasukkanUser $masukkan): UserResource
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $masukkan($kredensial, $request->boolean('remember'), $request->ip());

        $request->session()->regenerate();

        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Anda berhasil keluar.']);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
