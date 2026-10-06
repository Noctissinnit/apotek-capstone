"use client";

import { useEffect, useState } from "react";
import { api, ApiError } from "./api";

type Hasil<T> = { kunci: string; data?: T; error?: ApiError };

/**
 * Ambil data GET dari API. Setiap kali path atau query berubah, data diambil ulang.
 * Status "memuat" diturunkan dari kunci permintaan, bukan disetel di dalam effect.
 *
 * Isi path dengan null untuk menunda pengambilan (misalnya menunggu data user).
 */
export function useApi<T>(path: string | null, query?: Record<string, string | number | undefined>) {
  const kunci = path === null ? null : `${path}?${JSON.stringify(query ?? {})}`;
  const [hasil, setHasil] = useState<Hasil<T> | null>(null);
  const [ulang, setUlang] = useState(0);

  useEffect(() => {
    if (path === null || kunci === null) return;

    let batal = false;

    api<T>(path, { query })
      .then((data) => !batal && setHasil({ kunci, data }))
      .catch((error: unknown) => {
        if (batal) return;
        setHasil({
          kunci,
          error: error instanceof ApiError ? error : new ApiError(0, "Terjadi kesalahan yang tidak diketahui."),
        });
      });

    return () => {
      batal = true;
    };
    // kunci sudah mewakili path dan query
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [kunci, ulang]);

  const cocok = hasil !== null && hasil.kunci === kunci;

  return {
    data: cocok ? hasil.data : undefined,
    error: cocok ? hasil.error : undefined,
    memuat: kunci !== null && !cocok,
    muatUlang: () => setUlang((n) => n + 1),
  };
}
