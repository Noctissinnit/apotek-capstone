@extends('layouts.app')

@section('title', 'Edit User')
@section('header', 'Edit User')
@section('subheader', 'Perbarui data dan role user')

@section('content')
    @include('users.form', ['action' => route('user.update', $user), 'method' => 'PUT', 'submitLabel' => 'Simpan Perubahan'])
@endsection
