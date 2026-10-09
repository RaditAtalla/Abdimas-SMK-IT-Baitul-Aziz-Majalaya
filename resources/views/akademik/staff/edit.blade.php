@extends('layouts.master')

@section('title', 'Edit Staff')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')
    <x-breadcrumb item="Manajemen Staf" link="{{ route('akademik.staff.index') }}" active="Edit Staf" />

    <div class="container">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Staf</h5>
                    <span class="d-block m-t-5">Edit data akun Guru Mapel atau Wali Kelas</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('akademik.staff.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label>Nama</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- <div class="mb-3">
                            <label>NUPTK / Kredensial Unik</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                class="form-control @error('username') is-invalid @enderror" required>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div> --}}
                        <div class="mb-3">
                            <label>Password (kosongkan jika tidak diubah)</label>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label>Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                class="form-control @error('password_confirmation') is-invalid @enderror">
                            @error('password_confirmation')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-control @error('role') is-invalid @enderror">
                                <option value="">Pilih Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ strtolower($role->name) }}"
                                        {{ $user->roles->first() && strtolower($user->roles->first()->name) == strtolower($role->name) ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div> --}}

                        <div class="mb-3">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror"
                                required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="l"
                                    {{ old('jenis_kelamin', $staff->jenis_kelamin) == 'l' ? 'selected' : '' }}>Laki-laki
                                </option>
                                <option value="p"
                                    {{ old('jenis_kelamin', $staff->jenis_kelamin) == 'p' ? 'selected' : '' }}>Perempuan
                                </option>
                            </select>
                            @error('jenis_kelamin')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button class="btn btn-success">Simpan</button>
                        <a href="{{ route('akademik.staff.index') }}" class="btn btn-light-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- [Page Specific JS] start -->
    <script type="module">
        import {
            DataTable
        } from '/build/js/plugins/module.js';
        window.dt = new DataTable('#pc-dt-simple');
    </script>
    <!-- [Page Specific JS] end -->
@endsection
