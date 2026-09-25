@extends('layouts.app')

@section('title', 'Edit User')
@section('header', 'Edit User')
@section('subheader', 'Perbarui data dan role user')

@section('content')
    @include('admin.users.form', ['action' => route('admin.user.update', $user), 'method' => 'PUT', 'submitLabel' => 'Simpan Perubahan'])
@endsection
