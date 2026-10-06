<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.kategori.index', [
            // kategori dipakai bersama, tapi jumlah obatnya hanya milik apotek sendiri
            'kategori' => Kategori::withCount(['obat' => fn ($query) => $query->forUser($request->user())])
                ->orderBy('nama_kategori')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.kategori.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Kategori::create($request->validate(Kategori::aturan()));

        return to_route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Kategori $kategori): View
    {
        return view('admin.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $kategori->update($request->validate(Kategori::aturan($kategori)));

        return to_route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->masihDipakai()) {
            return to_route('kategori.index')->with('error', 'Kategori yang masih digunakan obat tidak dapat dihapus.');
        }

        $kategori->delete();

        return to_route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
