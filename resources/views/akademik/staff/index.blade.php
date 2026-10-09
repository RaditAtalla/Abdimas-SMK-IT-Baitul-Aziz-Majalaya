@extends('layouts.master')

@section('title', 'Manajemen Guru')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')
    <x-breadcrumb item="Manajemen Staf" active="Manajemen Staf" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Daftar Staff</h5>
                        <span class="d-block m-t-5">Manajemen Guru Mapel & Wali Kelas</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('akademik.staff.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Tambah Guru
                        </a>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                                        <td>
                                            <a href="{{ route('akademik.staff.edit', $user->id) }}"
                                                class="btn btn-sm btn-light-warning mb-1">Edit</a>

                                            <form action="{{ route('akademik.staff.destroy', $user->id) }}" method="POST"
                                                class="d-inline form-delete-staff">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-light-danger mb-1">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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

    <script>
        // Konfirmasi hapus staff (event delegation agar support DataTables)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.form-delete-staff').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!window.confirm('Yakin ingin menghapus staff ini?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endsection
