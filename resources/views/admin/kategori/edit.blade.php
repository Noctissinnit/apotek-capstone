@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('header', 'Edit Kategori')
@section('content')<form method="POST" action="{{ route('kategori.update', $kategori) }}" class="max-w-xl rounded-xl border border-slate-200 bg-white p-5">@method('PUT') @include('admin.kategori._form', ['submitLabel' => 'Perbarui'])</form>@endsection