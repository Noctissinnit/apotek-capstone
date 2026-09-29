<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /** Role admin untuk kedua apotek. */
    private const ROLE_ADMIN = ['admin_apotek_a', 'admin_apotek_b'];

    /** Role kasir untuk kedua apotek. */
    private const ROLE_KASIR = ['kasir_apotek_a', 'kasir_apotek_b'];

    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('roles')->latest()->paginate(10),
            'totalUser' => User::count(),
            'totalAdmin' => User::role(self::ROLE_ADMIN)->count(),
            'totalKasir' => User::role(self::ROLE_KASIR)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['user' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $role = $this->namaRole($data['jabatan'], $data['apotek']);
        unset($data['jabatan']);

        $user = User::create($data);
        $user->syncRoles($role);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $role = $this->namaRole($data['jabatan'], $data['apotek']);
        unset($data['jabatan']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($role);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        if ($user->isAdmin() && User::role(self::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'Admin terakhir tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'kontak' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->getKey())],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'apotek' => ['required', Rule::in(['Apotek A', 'Apotek B'])],
            'jabatan' => ['required', Rule::in(['admin', 'kasir'])],
        ]);
    }

    /**
     * Gabungkan jabatan dan apotek menjadi nama role, misalnya "kasir_apotek_b".
     */
    private function namaRole(string $jabatan, string $apotek): string
    {
        return $jabatan.'_apotek_'.strtolower(str_replace('Apotek ', '', $apotek));
    }
}
