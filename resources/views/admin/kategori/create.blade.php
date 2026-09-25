@extends('layouts.app')
@section('title', 'Tambah Kategori')
@section('header', 'Tambah Kategori')
@section('content')<form method="POST" action="{{ route('kategori.store') }}" class="max-w-xl rounded-xl border border-slate-200 bg-white p-5">@include('admin.kategori._form', ['submitLabel' => 'Simpan'])</form>@endsection