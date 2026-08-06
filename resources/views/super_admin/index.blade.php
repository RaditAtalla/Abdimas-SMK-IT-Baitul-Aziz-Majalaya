@extends('layouts.master')

@section('title', 'Manajemen User')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')

    <x-breadcrumb item="Superadmin" active="Manajemen Pengguna" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card">

                {{-- Header --}}
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Manajemen User</h5>
                        <span class="d-block m-t-5">Kelola data user & role</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Tambah User
                        </a>
                    </div>
                </div>

                {{-- Body --}}
                <div class="card-body table-border-style">
                    @if (session('success'))
                        <div class="alert alert-success mb-3">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Kredensial</th>
                                    <th>Role</th>
                                    {{-- kalau nanti mau ada aksi, buka ini --}}
                                    {{-- <th>Actions</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->roles->pluck('name')->join(', ') }}</td>

                                        {{-- contoh actions (opsional)
                    <td>
                      <a href="{{ route('superadmin.users.edit', $user->id) }}" class="btn btn-sm btn-light-primary">
                        Edit
                      </a>
                    </td>
                    --}}
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
@endsection
