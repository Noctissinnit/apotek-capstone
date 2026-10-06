"use client";

import { useState } from "react";
import { JudulHalaman, PesanError } from "@/components/judul-halaman";
import { Pagination } from "@/components/pagination";
import { useAuth } from "@/lib/auth";
import { angka, rupiah } from "@/lib/format";
import type { Obat, Paginasi } from "@/lib/types";
import { useApi } from "@/lib/use-api";

/**
 * Contoh pola tabel data: pencarian, urutan, dan pagination dari API.
 * Halaman lain bisa meniru susunan ini.
 */
export default function HalamanObat() {
  const { boleh } = useAuth();
  const [ketikan, setKetikan] = useState("");
  const [saringan, setSaringan] = useState({ search: "", page: 1, sort: "nama" });

  const { data, error, memuat } = useApi<Paginasi<Obat>>("/api/v1/obat", {
    search: saringan.search,
    page: saringan.page,
    sort: saringan.sort,
    per_page: 10,
  });

  // kolom harga beli hanya dikirim API untuk pengelola
  const pengelola = boleh("obat.kelola");

  return (
    <>
      <JudulHalaman judul="Data Obat" keterangan="Daftar obat milik apotek Anda" />

      <form
        className="mb-4 flex flex-wrap gap-2"
        onSubmit={(e) => {
          e.preventDefault();
          setSaringan((s) => ({ ...s, search: ketikan.trim(), page: 1 }));
        }}
      >
        <label htmlFor="cari" className="sr-only">
          Cari obat
        </label>
        <input id="cari" type="search" value={ketikan} onChange={(e) => setKetikan(e.target.value)} placeholder="Cari kode, nama, atau kategori..." className="isian max-w-sm" />
        <label htmlFor="urut" className="sr-only">
          Urutkan
        </label>
        <select id="urut" value={saringan.sort} onChange={(e) => setSaringan((s) => ({ ...s, sort: e.target.value, page: 1 }))} className="isian w-auto">
          <option value="nama">Nama</option>
          <option value="kode">Kode</option>
          <option value="stok">Stok</option>
          <option value="kategori">Kategori</option>
          <option value="harga_jual">Harga jual</option>
        </select>
        <button type="submit" className="tombol-utama">
          Cari
        </button>
      </form>

      {error && <PesanError pesan={error.message} />}

      <div className="kartu overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full text-left text-sm">
            <thead className="bg-slate-50 text-xs text-slate-500 uppercase">
              <tr>
                <th className="px-5 py-3 font-medium">Kode</th>
                <th className="px-5 py-3 font-medium">Nama</th>
                <th className="px-5 py-3 font-medium">Kategori</th>
                <th className="px-5 py-3 text-right font-medium">Stok</th>
                <th className="px-5 py-3 text-right font-medium">Siap dijual</th>
                {pengelola && <th className="px-5 py-3 text-right font-medium">Harga beli</th>}
                <th className="px-5 py-3 text-right font-medium">Harga jual</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {memuat && (
                <tr>
                  <td colSpan={7} className="px-5 py-8 text-center text-slate-500">
                    Memuat...
                  </td>
                </tr>
              )}
              {!memuat && data?.data.length === 0 && (
                <tr>
                  <td colSpan={7} className="px-5 py-8 text-center text-slate-500">
                    {saringan.search ? `Tidak ada obat yang cocok dengan "${saringan.search}".` : "Belum ada data obat."}
                  </td>
                </tr>
              )}
              {!memuat &&
                data?.data.map((obat) => (
                  <tr key={obat.id}>
                    <td className="px-5 py-3 font-mono text-slate-500">{obat.kode_obat}</td>
                    <td className="px-5 py-3 font-medium text-slate-900">{obat.nama_obat}</td>
                    <td className="px-5 py-3 text-slate-600">{obat.kategori?.nama ?? "-"}</td>
                    <td className="px-5 py-3 text-right">
                      {obat.stok_menipis && <span className="lencana mr-2 bg-amber-50 text-amber-700">Menipis</span>}
                      {angka(obat.stok)} {obat.satuan}
                    </td>
                    <td className="px-5 py-3 text-right">{angka(obat.stok_siap_dijual ?? 0)}</td>
                    {pengelola && <td className="px-5 py-3 text-right">{rupiah(obat.harga_beli ?? 0)}</td>}
                    <td className="px-5 py-3 text-right">{rupiah(obat.harga_jual)}</td>
                  </tr>
                ))}
            </tbody>
          </table>
        </div>

        {data && <Pagination meta={data.meta} pindah={(page) => setSaringan((s) => ({ ...s, page }))} />}
      </div>
    </>
  );
}
