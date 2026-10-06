import type { Paginasi } from "@/lib/types";

/**
 * Navigasi halaman untuk daftar dari API (bentuk meta bawaan Laravel).
 * Tidak tampil bila datanya muat dalam satu halaman, sama seperti di web.
 */
export function Pagination({ meta, pindah }: { meta: Paginasi<unknown>["meta"]; pindah: (halaman: number) => void }) {
  if (meta.last_page <= 1) return null;

  return (
    <nav className="flex items-center justify-between gap-3 border-t border-slate-200 px-5 py-3 text-sm" aria-label="Pindah halaman">
      <p className="text-slate-500">
        {meta.from}–{meta.to} dari {meta.total}
      </p>
      <div className="flex items-center gap-2">
        <button type="button" className="tombol-biasa" disabled={meta.current_page <= 1} onClick={() => pindah(meta.current_page - 1)}>
          Sebelumnya
        </button>
        <span className="text-slate-600">
          {meta.current_page} / {meta.last_page}
        </span>
        <button type="button" className="tombol-biasa" disabled={meta.current_page >= meta.last_page} onClick={() => pindah(meta.current_page + 1)}>
          Berikutnya
        </button>
      </div>
    </nav>
  );
}
