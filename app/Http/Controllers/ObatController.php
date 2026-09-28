<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $sortableColumns = [
            'kode' => 'kode_obat',
            'nama' => 'nama_obat',
            'stok' => 'stok',
            'kategori' => 'kategori.nama_kategori',
            'satuan' => 'satuan',
            'harga_jual' => 'harga_jual',
        ];
        $sortOption = $request->string('sort_option')->toString();
        $sort = $request->string('sort_by')->toString() ?: $request->string('sort')->toString();
        $direction = $request->string('direction')->lower()->toString();

        if ($sortOption !== '') {
            [$sort, $direction] = array_pad(explode('|', $sortOption, 2), 2, 'asc');
        }

        $sort = array_key_exists($sort, $sortableColumns) ? $sort : 'kode';
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $obat = Obat::query()
            ->forUser($user)
            ->with('kategoriRelasi')
            ->when($request->filled('kategori_id'), fn($query) => $query->where('obat.kategori_id', $request->integer('kategori_id')))
            ->when($request->filled('satuan'), fn($query) => $query->where('satuan', $request->string('satuan')->toString()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();

                $query->where(function ($query) use ($search) {
                    $query->where('kode_obat', 'like', "%{$search}%")
                        ->orWhere('nama_obat', 'like', "%{$search}%")
                        ->orWhereHas('kategoriRelasi', fn($query) => $query->where('nama_kategori', 'like', "%{$search}%"));
                });
            })
            ->when($sort === 'kategori', fn($query) => $query->leftJoin('kategori', 'obat.kategori_id', '=', 'kategori.id_kategori')->select('obat.*'))
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        $kategori = Kategori::orderBy('nama_kategori')->get();
        $satuan = Obat::query()->forUser($user)->whereNotNull('satuan')->distinct()->orderBy('satuan')->pluck('satuan');

        return view('admin.obat.index', compact('obat', 'sort', 'direction', 'kategori', 'satuan'));
    }

    public function create(): View
    {
        return view('admin.obat.create', ['kategori' => Kategori::orderBy('nama_kategori')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Obat::create($this->validatedData($request));

        return to_route('obat.index')->with('success', 'Data obat berhasil ditambahkan.');
    }

    public function show(Obat $obat): View
    {
        $this->ensureSameApotek($obat);

        return view('admin.obat.show', compact('obat'));
    }

    public function edit(Obat $obat): View
    {
        $this->ensureSameApotek($obat);

        return view('admin.obat.edit', ['obat' => $obat, 'kategori' => Kategori::orderBy('nama_kategori')->get()]);
    }

    public function update(Request $request, Obat $obat): RedirectResponse
    {
        $this->ensureSameApotek($obat);
        $obat->update($this->validatedData($request, $obat));

        return to_route('obat.index')->with('success', 'Data obat berhasil diperbarui.');
    }

    public function destroy(Obat $obat): RedirectResponse
    {
        $this->ensureSameApotek($obat);
        $obat->delete();

        return to_route('obat.index')->with('success', 'Data obat berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Obat $obat = null): array
    {
        $data = $request->validate([
            'kode_obat' => ['required', 'string', 'max:20', 'unique:obat,kode_obat,' . ($obat?->id ?? 'NULL') . ',id'],
            'nama_obat' => ['required', 'string', 'max:255'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori,id_kategori'],
            'satuan' => ['required', 'string', 'max:20'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'tanggal_kadaluarsa' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $data['apotek'] = $request->user()->apotek;

        return $data;
    }

    private function ensureSameApotek(Obat $obat): void
    {
        $user = request()->user();
        $isLegacyAdminRecord = $obat->apotek === null && $user->isAdmin();

        abort_unless($obat->apotek === $user->apotek || $isLegacyAdminRecord, 404);
    }
}
