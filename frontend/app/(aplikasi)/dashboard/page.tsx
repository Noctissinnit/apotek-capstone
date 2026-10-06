"use client";

import { JudulHalaman, PesanError } from "@/components/judul-halaman";
import { useAuth } from "@/lib/auth";
import { angka, rupiah, tanggal } from "@/lib/format";
import type { DashboardAdmin, DashboardKasir } from "@/lib/types";
import { useApi } from "@/lib/use-api";

export default function HalamanDashboard() {
  const { user } = useAuth();

  return (
    <>
      <JudulHalaman judul={user?.jenis === "admin" ? "Dashboard Admin" : "Dashboard Kasir"} keterangan={`Selamat bekerja, ${user?.name}`} />
      {user?.jenis === "admin" ? <DashboardUntukAdmin /> : <DashboardUntukKasir />}
    </>
  );
}

function KartuAngka({ label, nilai, keterangan, warna = "text-slate-900" }: { label: string; nilai: string; keterangan?: string; warna?: string }) {
  return (
    <div className="kartu p-4">
      <p className="text-xs font-medium tracking-wide text-slate-500 uppercase">{label}</p>
      <p className={`mt-1.5 text-2xl font-semibold tabular-nums ${warna}`}>{nilai}</p>
      {keterangan && <p className="mt-1 text-xs text-slate-500">{keterangan}</p>}
    </div>
  );
}

function DashboardUntukAdmin() {
  const { data, error, memuat } = useApi<{ data: DashboardAdmin }>("/api/v1/dashboard/admin");

  if (error) return <PesanError pesan={error.message} />;
  if (memuat || !data) return <p className="text-sm text-slate-500">Memuat ringkasan...</p>;

  const d = data.data;

  return (
    <>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <KartuAngka label="Jenis Obat" nilai={angka(d.total_obat)} />
        <KartuAngka label="Stok Menipis" nilai={angka(d.stok_menipis)} warna={d.stok_menipis ? "text-amber-600" : undefined} />
        <KartuAngka label="Supplier" nilai={angka(d.total_supplier)} />
        <KartuAngka label="Pembelian Bulan Ini" nilai={rupiah(d.total_pembelian_bulan_ini)} />
      </div>

      <section className="kartu mt-6">
        <h2 className="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900">Pembelian Terbaru</h2>
        {d.pembelian_terbaru.length === 0 ? (
          <p className="px-5 py-8 text-center text-sm text-slate-500">Belum ada pembelian.</p>
        ) : (
          <ul className="divide-y divide-slate-100">
            {d.pembelian_terbaru.map((faktur) => (
              <li key={faktur.id} className="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                <div>
                  <p className="font-medium text-slate-900">{faktur.no_faktur}</p>
                  <p className="text-xs text-slate-500">
                    {faktur.supplier} · {tanggal(faktur.tanggal_pembelian)}
                  </p>
                </div>
                <span className="tabular-nums">{rupiah(faktur.total)}</span>
              </li>
            ))}
          </ul>
        )}
      </section>
    </>
  );
}

function DashboardUntukKasir() {
  const { data, error, memuat } = useApi<{ data: DashboardKasir }>("/api/v1/dashboard/kasir");

  if (error) return <PesanError pesan={error.message} />;
  if (memuat || !data) return <p className="text-sm text-slate-500">Memuat ringkasan...</p>;

  const d = data.data;

  return (
    <>
      <div className="grid gap-4 sm:grid-cols-3">
        <KartuAngka label="Jenis Obat" nilai={angka(d.total_obat)} keterangan="Terdaftar di sistem" />
        <KartuAngka
          label="Stok Menipis"
          nilai={angka(d.obat_menipis.length)}
          keterangan="Stok di bawah batas minimum"
          warna={d.obat_menipis.length ? "text-amber-600" : undefined}
        />
        <KartuAngka
          label="Perlu Perhatian"
          nilai={angka(d.batch_perlu_perhatian.length)}
          keterangan="batch mendekati kadaluarsa"
          warna={d.batch_perlu_perhatian.length ? "text-red-600" : undefined}
        />
      </div>

      <section className="kartu mt-6">
        <h2 className="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900">Transaksi Terakhir</h2>
        {d.riwayat_terakhir.length === 0 ? (
          <p className="px-5 py-8 text-center text-sm text-slate-500">Belum ada transaksi.</p>
        ) : (
          <ul className="divide-y divide-slate-100">
            {d.riwayat_terakhir.map((trx) => (
              <li key={trx.id} className="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                <div className="min-w-0">
                  <p className="truncate font-medium text-slate-900">{trx.no_faktur}</p>
                  <p className="truncate text-xs text-slate-500">{trx.item?.map((baris) => `${baris.nama_obat} ×${baris.jumlah}`).join(", ")}</p>
                </div>
                <span className="shrink-0 tabular-nums">{rupiah(trx.total)}</span>
              </li>
            ))}
          </ul>
        )}
      </section>
    </>
  );
}
