export function JudulHalaman({ judul, keterangan, children }: { judul: string; keterangan?: string; children?: React.ReactNode }) {
  return (
    <div className="mb-5 flex flex-wrap items-end justify-between gap-3 border-b border-slate-200 pb-4">
      <div>
        <h1 className="text-lg font-semibold tracking-tight text-slate-900">{judul}</h1>
        {keterangan && <p className="mt-0.5 text-sm text-slate-500">{keterangan}</p>}
      </div>
      {children && <div className="flex items-center gap-2">{children}</div>}
    </div>
  );
}

export function PesanError({ pesan }: { pesan: string }) {
  return (
    <div role="alert" className="mb-4 rounded-md border-l-2 border-red-600 bg-red-50 px-3 py-2.5 text-sm text-red-900">
      {pesan}
    </div>
  );
}
