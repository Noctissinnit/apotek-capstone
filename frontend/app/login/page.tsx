"use client";

import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { ApiError } from "@/lib/api";
import { useAuth } from "@/lib/auth";

export default function HalamanLogin() {
  const { user, memuat, login } = useAuth();
  const router = useRouter();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [ingat, setIngat] = useState(false);
  const [lihatPassword, setLihatPassword] = useState(false);
  const [mengirim, setMengirim] = useState(false);
  const [gagal, setGagal] = useState<ApiError | null>(null);

  // yang sudah login tidak perlu melihat halaman ini
  useEffect(() => {
    if (!memuat && user) router.replace("/dashboard");
  }, [memuat, user, router]);

  async function kirim(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setMengirim(true);
    setGagal(null);

    try {
      await login(email, password, ingat);
      router.replace("/dashboard");
    } catch (error) {
      setGagal(error instanceof ApiError ? error : new ApiError(0, "Terjadi kesalahan."));
    } finally {
      setMengirim(false);
    }
  }

  const pesanEmail = gagal?.kolom("email");
  const pesanPassword = gagal?.kolom("password");
  // error tanpa kolom, misalnya server mati
  const pesanUmum = gagal && !pesanEmail && !pesanPassword ? gagal.message : null;

  return (
    <main className="flex min-h-screen items-center justify-center px-4 py-8">
      <div className="kartu grid w-full max-w-4xl overflow-hidden rounded-2xl p-2.5 shadow-[0_30px_70px_-30px_rgb(15_23_42/0.25)] lg:grid-cols-2">
        {/* Panel ilustrasi, disembunyikan di layar kecil */}
        <div className="relative hidden min-h-[34rem] overflow-hidden rounded-xl lg:block" aria-hidden="true">
          <svg className="absolute inset-0 h-full w-full" viewBox="0 0 400 560" preserveAspectRatio="xMidYMid slice">
            <defs>
              <linearGradient id="langit" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stopColor="#065f46" />
                <stop offset="0.55" stopColor="#10b981" />
                <stop offset="1" stopColor="#a7f3d0" />
              </linearGradient>
              <linearGradient id="bukit-jauh" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stopColor="#34d399" />
                <stop offset="1" stopColor="#059669" />
              </linearGradient>
              <linearGradient id="bukit-dekat" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stopColor="#047857" />
                <stop offset="0.6" stopColor="#059669" />
                <stop offset="1" stopColor="#064e3b" />
              </linearGradient>
            </defs>
            <rect width="400" height="560" fill="url(#langit)" />
            <path d="M0 330 C 70 300, 120 250, 190 235 C 250 222, 300 270, 400 300 L400 560 L0 560Z" fill="url(#bukit-jauh)" />
            <path d="M0 400 C 60 370, 130 300, 215 285 C 260 300, 300 360, 400 380 L400 560 L0 560Z" fill="url(#bukit-dekat)" />
            <path d="M215 285 C 240 330, 250 400, 230 560 L400 560 L400 380 C 300 360, 260 300, 215 285Z" fill="#064e3b" opacity="0.35" />
            <rect y="420" width="400" height="140" fill="#022c22" opacity="0.25" />
          </svg>

          <div className="absolute inset-x-0 top-0 flex items-center justify-between p-6">
            <span className="text-xl font-semibold tracking-[0.2em] text-white">APOTEK</span>
            <span className="rounded-full bg-white/15 px-3 py-1 text-xs text-white/90 backdrop-blur">Sistem Informasi Apotek</span>
          </div>

          <p className="absolute inset-x-0 bottom-0 p-8 text-center text-2xl leading-snug font-medium text-white">
            Stok Tercatat,
            <br />
            Pelayanan Tenang
          </p>
        </div>

        {/* Form masuk */}
        <div className="flex flex-col justify-center px-6 py-10 sm:px-10">
          <h1 className="text-3xl font-medium tracking-tight text-slate-900">Masuk ke akun</h1>
          <p className="mt-3 text-sm text-slate-500">Belum punya akun? Minta dibuatkan oleh admin apotek.</p>

          {pesanUmum && (
            <div role="alert" className="mt-6 rounded-md border-l-2 border-red-600 bg-red-50 px-3 py-2.5 text-sm text-red-900">
              {pesanUmum}
            </div>
          )}

          <form onSubmit={kirim} className="mt-8 space-y-4" noValidate>
            <div>
              <label htmlFor="email" className="sr-only">
                Email
              </label>
              <input
                id="email"
                type="email"
                autoComplete="username"
                autoFocus
                required
                placeholder="Email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                aria-invalid={pesanEmail ? true : undefined}
                aria-describedby={pesanEmail ? "email-error" : undefined}
                className={`isian bg-slate-50 py-2.5 focus:bg-white ${pesanEmail ? "border-red-400" : ""}`}
              />
              {pesanEmail && (
                <p id="email-error" className="mt-1.5 text-xs text-red-600">
                  {pesanEmail}
                </p>
              )}
            </div>

            <div>
              <label htmlFor="password" className="sr-only">
                Password
              </label>
              <div className="relative">
                <input
                  id="password"
                  type={lihatPassword ? "text" : "password"}
                  autoComplete="current-password"
                  required
                  placeholder="Masukkan password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  aria-invalid={pesanPassword ? true : undefined}
                  aria-describedby={pesanPassword ? "password-error" : undefined}
                  className={`isian bg-slate-50 py-2.5 pr-11 focus:bg-white ${pesanPassword ? "border-red-400" : ""}`}
                />
                <button
                  type="button"
                  onClick={() => setLihatPassword((v) => !v)}
                  aria-pressed={lihatPassword}
                  aria-label={lihatPassword ? "Sembunyikan password" : "Tampilkan password"}
                  className="absolute top-1/2 right-2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                >
                  <svg className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                </button>
              </div>
              {pesanPassword && (
                <p id="password-error" className="mt-1.5 text-xs text-red-600">
                  {pesanPassword}
                </p>
              )}
            </div>

            <label className="flex items-center gap-2.5 pt-1 text-sm text-slate-600">
              <input type="checkbox" checked={ingat} onChange={(e) => setIngat(e.target.checked)} className="h-4 w-4 rounded accent-emerald-700" />
              Ingat saya di perangkat ini
            </label>

            <button type="submit" disabled={mengirim} className="tombol-utama mt-4 w-full py-2.5">
              {mengirim ? "Memeriksa..." : "Masuk"}
            </button>
          </form>
        </div>
      </div>
    </main>
  );
}
