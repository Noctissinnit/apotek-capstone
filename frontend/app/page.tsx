import { redirect } from "next/navigation";

// Halaman awal langsung ke dashboard; yang belum login dialihkan lagi ke /login.
export default function Beranda() {
  redirect("/dashboard");
}
