@extends('layouts.app')

@section('title', 'Tambah User')
@section('header', 'Tambah User')
@section('subheader', 'Buat akun baru dan tentukan role aksesnya')

@section('content')
    @include('admin.users.form', ['action' => route('admin.user.store'), 'method' => 'POST', 'submitLabel' => 'Simpan User'])
@endsection
