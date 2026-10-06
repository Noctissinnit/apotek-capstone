"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import type { User } from "@/lib/types";

type Menu = {
  label: string;
  href: string;
  /** izin Spatie yang harus dimiliki, sama dengan middleware route API-nya */
  izin?: string;
  ikon: string;
};

/**
 * Menu hanya berisi halaman yang sudah dibuat di frontend ini. Tambahkan baris
 * baru di sini setelah halamannya ada, supaya user tidak menemui halaman kosong.
 */
const MENU: { judul: string; isi: Menu[] }[] = [
  {
    judul: "Utama",
    isi: [
      {
        label: "Dashboard",
        href: "/dashboard",
        izin: "dashboard.lihat",
        ikon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
      },
    ],
  },
  {
    judul: "Master Data",
    isi: [
      {
        label: "Data Obat",
        href: "/obat",
        izin: "obat.lihat",
        ikon: "M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z",
      },
    ],
  },
];

export function Sidebar({ user, terbuka, tutup }: { user: User; terbuka: boolean; tutup: () => void }) {
  const pathname = usePathname();

  return (
    <>
      {/* latar gelap di layar kecil saat sidebar terbuka */}
      {terbuka && <div className="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" onClick={tutup} aria-hidden="true" />}

      <aside
        className={`fixed inset-y-0 left-0 z-40 flex w-60 flex-col bg-slate-900 transition-transform duration-200 lg:translate-x-0 ${
          terbuka ? "translate-x-0" : "-translate-x-full"
        }`}
      >
        <div className="flex h-14 items-center gap-2.5 border-b border-slate-800 px-4">
          <div className="flex h-7 w-7 items-center justify-center rounded bg-emerald-700 text-xs font-bold text-white">A</div>
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold text-white">Apotek</p>
            <p className="truncate text-xs text-slate-400">{user.apotek}</p>
          </div>
        </div>

        <nav className="flex-1 overflow-y-auto px-3 pb-6">
          {MENU.map(({ judul, isi }) => {
            // judul grup ikut hilang kalau seluruh menu di dalamnya tersembunyi
            const terlihat = isi.filter((menu) => !menu.izin || user.izin.includes(menu.izin));
            if (terlihat.length === 0) return null;

            return (
              <div key={judul}>
                <p className="px-3 pt-5 pb-1.5 text-[0.6875rem] font-semibold tracking-[0.08em] text-slate-500 uppercase">{judul}</p>
                {terlihat.map((menu) => {
                  const aktif = pathname === menu.href || pathname.startsWith(`${menu.href}/`);

                  return (
                    <Link
                      key={menu.href}
                      href={menu.href}
                      onClick={tutup}
                      aria-current={aktif ? "page" : undefined}
                      className={`flex items-center gap-2.5 rounded-md px-3 py-2 text-sm transition ${
                        aktif
                          ? "bg-slate-800 font-medium text-white shadow-[inset_2px_0_0_0_var(--color-aksen)]"
                          : "text-slate-300 hover:bg-slate-800 hover:text-white"
                      }`}
                    >
                      <svg className="h-5 w-5 shrink-0" fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path strokeLinecap="round" strokeLinejoin="round" d={menu.ikon} />
                      </svg>
                      <span>{menu.label}</span>
                    </Link>
                  );
                })}
              </div>
            );
          })}
        </nav>
      </aside>
    </>
  );
}
