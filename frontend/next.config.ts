import path from "node:path";
import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  turbopack: {
    // Repo induknya proyek Laravel yang juga punya package-lock.json.
    // Tanpa ini Next mengira root proyeknya folder Laravel.
    root: path.join(__dirname),
  },
};

export default nextConfig;
