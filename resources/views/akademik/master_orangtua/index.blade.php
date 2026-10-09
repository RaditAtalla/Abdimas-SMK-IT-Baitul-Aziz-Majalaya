@extends('layouts.master')

@section('title', 'Manajemen Data Orang Tua')

@section('content')
    <x-breadcrumb item="Manajemen Akun" active="Data Orang Tua" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Daftar Semua Orang Tua</h5>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form action="" method="GET" class="d-flex">
                            <input type="text" name="q" class="form-control me-2" placeholder="Cari Nama Ayah/Ibu..." value="{{ request('q') }}">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                        </form>
                    </div>
                </div>

                <div class="card-body table-border-style">
                    @if (session('success'))
                        <div class="alert alert-success mb-3">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Ayah</th>
                                    <th>Nama Ibu</th>
                                    <th>Pekerjaan Ayah</th>
                                    <th>Pekerjaan Ibu</th>
                                    <th>Domisili</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($orangTua as $ortu)
                                    <tr>
                                        <td>{{ $loop->iteration + $orangTua->firstItem() - 1 }}</td>
                                        <td>{{ $ortu->nama_ayah ?? '-' }}</td>
                                        <td>{{ $ortu->nama_ibu ?? '-' }}</td>
                                        <td>{{ $ortu->pekerjaan_ayah ?? '-' }}</td>
                                        <td>{{ $ortu->pekerjaan_ibu ?? '-' }}</td>
                                        <td>
                                            {{ $ortu->jalan ?? '-' }}, {{ $ortu->kelurahan?->nama ?? '-' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('akademik.master-orang-tua.edit', $ortu->orang_tua_id) }}"
                                                class="btn btn-sm btn-light-warning mb-1">Edit</a>
                                            <form action="{{ route('akademik.master-orang-tua.destroy', $ortu->orang_tua_id) }}"
                                                method="POST" style="display:inline"
                                                onsubmit="return confirm('Yakin ingin menghapus orang tua ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-light-danger mb-1">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada data orang tua.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $orangTua->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
