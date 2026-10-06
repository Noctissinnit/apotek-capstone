import type { Metadata } from "next";
import { Instrument_Sans } from "next/font/google";
import { AuthProvider } from "@/lib/auth";
import "./globals.css";

const instrumentSans = Instrument_Sans({
  variable: "--font-instrument-sans",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: {
    template: "%s - Apotek",
    default: "Apotek",
  },
  description: "Sistem Informasi Apotek",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="id" className={`${instrumentSans.variable} h-full`}>
      <body className="min-h-full font-sans">
        <AuthProvider>{children}</AuthProvider>
      </body>
    </html>
  );
}
