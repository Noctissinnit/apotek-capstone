@extends('layouts.app')
@section('title', 'Edit Obat')
@section('header', 'Edit Obat')
@section('subheader', 'Perbarui informasi obat')
@section('content')
<form method="POST" action="{{ route('obat.update', $obat) }}" class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
    @method('PUT')
    @include('admin.obat._form', ['submitLabel' => 'Perbarui'])
</form>
@endsection