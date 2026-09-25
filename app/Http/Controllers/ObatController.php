<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObatController extends Controller
{
    public function index(Request $request): View
    {
        $sortableColumns = [
            'kode' => 'kode_obat',
            'nama' => 'nama_obat',
            'stok' => 'stok',
            'kategori' => 'kategori',
            'satuan' => 'satuan',
            'harga_jual' => 'harga_jual',
        ];
        $sort = $request->string('sort')->toString();
        $sort = array_key_exists($sort, $sortableColumns) ? $sort : 'kode';
        $direction = $request->string('direction')->lower()->toString() === 'desc' ? 'desc' : 'asc';

        $obat = Obat::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();

                $query->where(function ($query) use ($search) {
                    $query->where('kode_obat', 'like', "%{$search}%")
                        ->orWhere('nama_obat', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.obat.index', compact('obat', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('admin.obat.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Obat::create($this->validatedData($request));

        return to_route('obat.index')->with('success', 'Data obat berhasil ditambahkan.');
    }

    public function show(Obat $obat): View
    {
        return view('admin.obat.show', compact('obat'));
    }

    public function edit(Obat $obat): View
    {
        return view('admin.obat.edit', compact('obat'));
    }

    public function update(Request $request, Obat $obat): RedirectResponse
    {
        $obat->update($this->validatedData($request, $obat));

        return to_route('obat.index')->with('success', 'Data obat berhasil diperbarui.');
    }

    public function destroy(Obat $obat): RedirectResponse
    {
        $obat->delete();

        return to_route('obat.index')->with('success', 'Data obat berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Obat $obat = null): array
    {
        return $request->validate([
            'kode_obat' => ['required', 'string', 'max:20', 'unique:obat,kode_obat,' . ($obat?->id ?? 'NULL') . ',id'],
            'nama_obat' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'satuan' => ['required', 'string', 'max:20'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'tanggal_kadaluarsa' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
        ]);
    }
}
