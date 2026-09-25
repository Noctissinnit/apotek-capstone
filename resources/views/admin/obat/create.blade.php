@extends('layouts.app')
@section('title', 'Tambah Obat')
@section('header', 'Tambah Obat')
@section('subheader', 'Tambahkan data obat baru')
@section('content')
<form method="POST" action="{{ route('obat.store') }}" class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
    @include('admin.obat._form', ['submitLabel' => 'Simpan'])
</form>
@endsection