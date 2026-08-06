@extends('layouts.master')

@section('title', 'Manajemen Akun')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')
    <x-breadcrumb item="Manajemen Akun" active="Manajemen Akun" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card">

                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Daftar Akun</h5>
                        <span class="d-block m-t-5">Manajemen semua role + status aktif/nonaktif</span>
                    </div>
                </div>

                <div class="card-body">
                    {{-- ALERT --}}
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- FILTER BAR --}}
                    <form method="GET" action="{{ route('akademik.akun.index') }}" class="mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label mb-1">Role</label>
                                <select class="form-select" name="role">
                                    <option value="all" @selected($role === 'all')>Semua Role</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->name }}" @selected($role === $r->name)>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label mb-1">Status Akun</label>
                                <select class="form-select" name="status">
                                    <option value="all" @selected($status === 'all')>Semua</option>
                                    <option value="active" @selected($status === 'active')>Aktif</option>
                                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                                </select>
                            </div>


                            <div class="col-md-2 text-end">
                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="ti ti-filter"></i> Terapkan
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th style="width:70px;">No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th style="width:140px;">Status</th>
                                    <th style="width:170px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $user->name }}
                                            @if($user->id === $currentUserId)
                                                <span class="badge bg-light-primary ms-2">Ini Anda</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email ?? '-' }}</td>
                                        <td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>

                                        <td>
                                            @if($user->is_active)
                                                <span class="badge bg-light-success">Aktif</span>
                                            @else
                                                <span class="badge bg-light-danger">Nonaktif</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{-- Tidak boleh nonaktifkan akun sendiri --}}
                                            @if($user->id === $currentUserId)
                                                <button class="btn btn-sm btn-light-secondary" disabled>
                                                    Tidak bisa ubah
                                                </button>
                                            @else
                                                <form action="{{ route('akademik.akun.toggle', $user->id) }}" method="POST" class="d-inline form-toggle-active">
                                                    @csrf
                                                    @method('PATCH')

                                                    @if($user->is_active)
                                                        <button type="submit" class="btn btn-sm btn-light-danger">
                                                            Nonaktifkan
                                                        </button>
                                                    @else
                                                        <button type="submit" class="btn btn-sm btn-light-success">
                                                            Aktifkan
                                                        </button>
                                                    @endif
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted text-center py-3">
                                            Data tidak ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        import { DataTable } from '/build/js/plugins/module.js';
        window.dt = new DataTable('#pc-dt-simple');
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.form-toggle-active').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!window.confirm('Yakin ingin mengubah status akun ini?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endsection
