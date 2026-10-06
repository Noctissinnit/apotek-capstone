/**
 * Pemanggil API Laravel (lihat docs/api.md di root repo).
 *
 * Login memakai cookie session Sanctum, jadi setiap request membawa cookie
 * (credentials: "include") dan request yang mengubah data membawa header
 * X-XSRF-TOKEN dari cookie XSRF-TOKEN.
 */

export const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000";

/** Error dari API: status HTTP, pesan utama, dan pesan per kolom (422). */
export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public errors: Record<string, string[]> = {},
  ) {
    super(message);
    this.name = "ApiError";
  }

  /** Pesan pertama untuk satu kolom isian, bila ada. */
  kolom(nama: string): string | undefined {
    return this.errors[nama]?.[0];
  }
}

type Opsi = {
  method?: "GET" | "POST" | "PUT" | "PATCH" | "DELETE";
  /** Objek biasa dikirim sebagai JSON; FormData dikirim apa adanya (untuk unggah gambar). */
  body?: unknown;
  query?: Record<string, string | number | undefined | null>;
};

function bacaCookie(nama: string): string | null {
  const baris = document.cookie.split("; ").find((isi) => isi.startsWith(`${nama}=`));

  return baris ? decodeURIComponent(baris.slice(nama.length + 1)) : null;
}

/** Minta Laravel memasang cookie XSRF-TOKEN. Wajib sebelum login. */
export async function siapkanCsrf(): Promise<void> {
  await fetch(`${API_URL}/sanctum/csrf-cookie`, { credentials: "include" });
}

function susunUrl(path: string, query?: Opsi["query"]): string {
  const url = new URL(path, API_URL);

  for (const [kunci, nilai] of Object.entries(query ?? {})) {
    if (nilai !== undefined && nilai !== null && nilai !== "") {
      url.searchParams.set(kunci, String(nilai));
    }
  }

  return url.toString();
}

export async function api<T>(path: string, opsi: Opsi = {}, sudahDiulang = false): Promise<T> {
  const method = opsi.method ?? "GET";
  const mengubahData = method !== "GET";

  if (mengubahData && !bacaCookie("XSRF-TOKEN")) {
    await siapkanCsrf();
  }

  const headers: Record<string, string> = { Accept: "application/json" };
  const token = bacaCookie("XSRF-TOKEN");
  if (token) headers["X-XSRF-TOKEN"] = token;

  let body: BodyInit | undefined;
  if (opsi.body instanceof FormData) {
    body = opsi.body;
  } else if (opsi.body !== undefined) {
    headers["Content-Type"] = "application/json";
    body = JSON.stringify(opsi.body);
  }

  let respon: Response;
  try {
    respon = await fetch(susunUrl(path, opsi.query), { method, headers, body, credentials: "include" });
  } catch {
    throw new ApiError(0, "Tidak dapat terhubung ke server. Periksa apakah API Laravel sudah berjalan.");
  }

  // token CSRF kedaluwarsa: ambil yang baru lalu ulangi sekali
  if (respon.status === 419 && !sudahDiulang) {
    await siapkanCsrf();
    return api<T>(path, opsi, true);
  }

  if (respon.status === 204) {
    return undefined as T;
  }

  const isi = await respon.json().catch(() => null);

  if (!respon.ok) {
    throw new ApiError(respon.status, isi?.message ?? "Terjadi kesalahan pada server.", isi?.errors ?? {});
  }

  return isi as T;
}
