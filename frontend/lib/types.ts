/**
 * Bentuk data dari API Laravel. Sumber kebenarannya ada di
 * app/Http/Resources/*.php dan docs/api.md di root repo.
 */

export type User = {
  id: number;
  name: string;
  email: string;
  apotek: string | null;
  jenis: "admin" | "kasir" | null;
  peran: string[];
  izin: string[];
};

export type Paginasi<T> = {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
  };
};

export type TingkatKadaluarsa = "kadaluarsa" | "segera_habis" | "siapkan_retur" | "aman";

export type Batch = {
  id: number;
  no_batch: string;
  obat?: { id: number; kode_obat: string; nama_obat: string; satuan: string };
  tanggal_kadaluarsa: string | null;
  sisa_hari: number | null;
  tingkat_kadaluarsa: TingkatKadaluarsa;
  stok: number;
  status: "aktif" | "ditahan";
  catatan_status: string | null;
  boleh_dijual: boolean;
  /** hanya untuk pengelola */
  modal_per_satuan?: number | null;
};

export type Obat = {
  id: number;
  kode_obat: string;
  nama_obat: string;
  gambar_url: string | null;
  apotek: string | null;
  kategori?: { id: number; nama: string } | null;
  satuan: string;
  isi_per_box: number;
  /** hanya untuk pengelola */
  harga_beli?: number;
  harga_jual: number;
  stok: number;
  stok_minimum: number;
  stok_menipis: boolean;
  stok_siap_dijual?: number;
  tanggal_kadaluarsa: string | null;
  keterangan: string | null;
  batch?: Batch[];
};

export type Pembelian = {
  id: number;
  no_faktur: string;
  tanggal_pembelian: string;
  supplier?: string | null;
  total: number;
};

export type Penjualan = {
  id: number;
  no_faktur: string;
  tanggal_penjualan: string;
  kasir?: string | null;
  metode_pembayaran: string;
  total: number;
  struk_url: string;
  item?: { obat_id: number; nama_obat: string | null; jumlah: number; subtotal: number }[];
};

export type DashboardAdmin = {
  total_obat: number;
  stok_menipis: number;
  total_supplier: number;
  total_pembelian_bulan_ini: number;
  pembelian_terbaru: Pembelian[];
};

export type DashboardKasir = {
  total_obat: number;
  obat_menipis: Obat[];
  batch_perlu_perhatian: Batch[];
  riwayat_terakhir: Penjualan[];
};
