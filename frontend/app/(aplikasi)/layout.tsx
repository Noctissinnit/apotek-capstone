"use client";

import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { Sidebar } from "@/components/sidebar";
import { useAuth } from "@/lib/auth";

/**
 * Kerangka untuk semua halaman yang butuh login. Cookie session milik domain
 * Laravel, jadi pemeriksaan login dilakukan di browser lewat GET /api/v1/me.
 * Penjagaan sebenarnya tetap di API: tanpa login semua endpoint membalas 401.
 */
export default function LayoutAplikasi({ children }: { children: React.ReactNode }) {
  const { user, memuat, logout } = useAuth();
  const router = useRouter();
  const [sidebarTerbuka, setSidebarTerbuka] = useState(false);

  useEffect(() => {
    if (!memuat && !user) router.replace("/login");
  }, [memuat, user, router]);

  if (!user) {
    return (
      <div className="flex min-h-screen items-center justify-center text-sm text-slate-500" role="status">
        Memuat...
      </div>
    );
  }

  async function keluar() {
    await logout();
    router.replace("/login");
  }

  return (
    <div className="flex min-h-screen">
      <Sidebar user={user} terbuka={sidebarTerbuka} tutup={() => setSidebarTerbuka(false)} />

      <div className="flex min-w-0 flex-1 flex-col lg:pl-60">
        <header className="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
          <button
            type="button"
            onClick={() => setSidebarTerbuka(true)}
            className="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
            aria-label="Buka menu"
          >
            <svg className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24" aria-hidden="true">
              <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <span className="hidden lg:block" />

          <div className="flex items-center gap-3">
            <div className="text-right">
              <p className="text-sm font-medium text-slate-900">{user.name}</p>
              <p className="text-xs text-slate-500">
                {user.jenis === "admin" ? "Admin" : "Kasir"} {user.apotek}
              </p>
            </div>
            <button type="button" onClick={keluar} className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-red-600" title="Keluar" aria-label="Keluar">
              <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24" aria-hidden="true">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                />
              </svg>
            </button>
          </div>
        </header>

        <main className="mx-auto w-full max-w-screen-2xl flex-1 px-4 py-5 sm:px-6">{children}</main>
      </div>
    </div>
  );
}
