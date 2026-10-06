"use client";

import { createContext, useCallback, useContext, useEffect, useState } from "react";
import { api, siapkanCsrf } from "./api";
import type { User } from "./types";

type Auth = {
  user: User | null;
  /** true selama status login belum diketahui */
  memuat: boolean;
  login: (email: string, password: string, ingat: boolean) => Promise<User>;
  logout: () => Promise<void>;
  /** Cek izin Spatie milik user, misalnya "obat.kelola". */
  boleh: (izin: string) => boolean;
};

const AuthContext = createContext<Auth | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [memuat, setMemuat] = useState(true);

  useEffect(() => {
    api<{ data: User }>("/api/v1/me")
      .then((respon) => setUser(respon.data))
      .catch(() => setUser(null))
      .finally(() => setMemuat(false));
  }, []);

  const login = useCallback(async (email: string, password: string, ingat: boolean) => {
    await siapkanCsrf();
    const respon = await api<{ data: User }>("/api/v1/login", {
      method: "POST",
      body: { email, password, remember: ingat },
    });
    setUser(respon.data);

    return respon.data;
  }, []);

  const logout = useCallback(async () => {
    // walau gagal (misalnya session sudah habis), di sisi frontend tetap dianggap keluar
    await api("/api/v1/logout", { method: "POST" }).catch(() => undefined);
    setUser(null);
  }, []);

  const boleh = useCallback((izin: string) => user?.izin.includes(izin) ?? false, [user]);

  return <AuthContext.Provider value={{ user, memuat, login, logout, boleh }}>{children}</AuthContext.Provider>;
}

export function useAuth(): Auth {
  const auth = useContext(AuthContext);

  if (!auth) {
    throw new Error("useAuth harus dipakai di dalam <AuthProvider>.");
  }

  return auth;
}
