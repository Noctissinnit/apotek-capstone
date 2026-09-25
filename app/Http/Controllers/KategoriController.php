<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        return view('admin.kategori.index', ['kategori' => Kategori::withCount('obat')->orderBy('nama_kategori')->get()]);
    }

    public function create(): View
    {
        return view('admin.kategori.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Kategori::create($this->validatedData($request));

        return to_route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Kategori $kategori): View
    {
        return view('admin.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $kategori->update($this->validatedData($request, $kategori));

        return to_route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->obat()->exists()) {
            return to_route('kategori.index')->with('error', 'Kategori yang masih digunakan obat tidak dapat dihapus.');
        }

        $kategori->delete();

        return to_route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Kategori $kategori = null): array
    {
        return $request->validate([
            'nama_kategori' => ['required', 'string', 'max:50', 'unique:kategori,nama_kategori,' . ($kategori?->id_kategori ?? 'NULL') . ',id_kategori'],
        ]);
    }
}
