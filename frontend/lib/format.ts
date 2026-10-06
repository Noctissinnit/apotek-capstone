const formatRupiah = new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  maximumFractionDigits: 0,
});

/** 13000 menjadi "Rp 13.000". */
export function rupiah(nilai: number): string {
  return formatRupiah.format(nilai).replace(/ /g, " ");
}

/** "2026-10-06" menjadi "6 Okt 2026". */
export function tanggal(nilai: string | null): string {
  if (!nilai) return "-";

  return new Date(nilai).toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
}

export function angka(nilai: number): string {
  return nilai.toLocaleString("id-ID");
}
