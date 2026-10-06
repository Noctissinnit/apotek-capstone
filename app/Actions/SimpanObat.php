<?php

namespace App\Actions;

use App\Models\Obat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Tambah dan ubah data master obat. Dipakai bersama halaman Data Obat dan API.
 */
class SimpanObat
{
    /**
     * Aturan isian obat. Gambar ikut diperiksa di sini, tetapi disimpan terpisah.
     *
     * @return array<string, mixed>
     */
    public static function aturan(?Obat $obat = null): array
    {
        return [
            'kode_obat' => ['required', 'string', 'max:20', Rule::unique('obat', 'kode_obat')->ignore($obat?->id)],
            'nama_obat' => ['required', 'string', 'max:255'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori,id_kategori'],
            'satuan' => ['required', Rule::in(Obat::SATUAN)],
            // 1 box berisi berapa satuan terkecil, dipakai saat barang masuk
            'isi_per_box' => ['required', 'integer', 'min:1', 'max:100000'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'tanggal_kadaluarsa' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
            // maksimal 2 MB, cukup untuk foto kemasan obat
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi dengan aturan()
     */
    public function buat(User $user, array $data, ?UploadedFile $gambar = null): Obat
    {
        $data = $this->rapikan($user, $data);
        $data['gambar'] = $gambar ? Storage::disk('unggahan')->putFile('obat', $gambar) : null;

        return DB::transaction(function () use ($data): Obat {
            $obat = Obat::create($data);

            // Stok yang diisi langsung di master dicatat sebagai batch stok awal,
            // supaya obat tetap bisa dijual walau belum pernah lewat barang masuk.
            if ($obat->stok > 0) {
                $obat->batch()->create([
                    'no_batch' => 'STOK-AWAL',
                    'tanggal_kadaluarsa' => $obat->tanggal_kadaluarsa,
                    'stok' => $obat->stok,
                    'modal_per_satuan' => $obat->harga_beli ?: null,
                ]);
            }

            return $obat;
        });
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi dengan aturan($obat)
     */
    public function ubah(User $user, Obat $obat, array $data, ?UploadedFile $gambar = null, bool $hapusGambar = false): Obat
    {
        $data = $this->rapikan($user, $data);
        $gambarLama = $obat->gambar;

        // Kalau stoknya sudah terpecah jadi batch, angka stok tidak boleh diubah
        // lewat form ini. Perubahannya harus lewat barang masuk atau penyesuaian stok.
        if ($obat->batch()->exists()) {
            unset($data['stok']);
        }

        if ($hapusGambar) {
            $data['gambar'] = null;
        }

        if ($gambar) {
            $data['gambar'] = Storage::disk('unggahan')->putFile('obat', $gambar);
        }

        $obat->update($data);

        // Berkas lama dibuang hanya setelah data tersimpan, agar tidak hilang bila gagal
        if ($gambarLama && $gambarLama !== $obat->gambar) {
            Storage::disk('unggahan')->delete($gambarLama);
        }

        return $obat;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function rapikan(User $user, array $data): array
    {
        // gambar diurus terpisah karena yang disimpan nama berkasnya, bukan isinya
        unset($data['gambar']);

        $data['apotek'] = $user->apotek;

        return $data;
    }
}
