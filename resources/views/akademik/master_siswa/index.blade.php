@extends('layouts.master')

@section('title', 'Manajemen Data Siswa')

@section('content')
    <x-breadcrumb item="Manajemen Siswa" active="Data Siswa" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Daftar Semua Siswa</h5>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form action="" method="GET" class="d-flex me-1">
                            <input type="text" name="q" class="form-control me-2" placeholder="Cari Nama/NIS/NISN..." value="{{ request('q') }}">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                        </form>
                        
                        <a href="{{ route('akademik.master-siswa.download-template') }}" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-excel me-1"></i> Download Template
                        </a>

                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalImportSiswa">
                            <i class="bi bi-upload me-1"></i> Import Excel
                        </button>

                        <a href="{{ route('akademik.master-siswa.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Siswa
                        </a>
                    </div>
                </div>

                <div class="card-body table-border-style">
                    @if (session('success'))
                        <div class="alert alert-success mb-3">{{ session('success') }}</div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning mb-3">{{ session('warning') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>NIS / NISN</th>
                                    <th>Orang Tua</th>
                                    <th>Domisili</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($siswa as $s)
                                    <tr>
                                        <td>{{ $loop->iteration + $siswa->firstItem() - 1 }}</td>
                                        <td>{{ $s->user?->name ?? ($s->nama ?? '-') }}</td>
                                        <td>{{ $s->user?->username ?? '-' }}</td>
                                        <td>{{ $s->nis ?? '-' }} / {{ $s->nisn ?? '-' }}</td>
                                        <td>{{ $s->orangTua?->nama_ayah ?? '-' }} / {{ $s->orangTua?->nama_ibu ?? '-' }}</td>
                                        <td>
                                            {{ $s->kelurahan?->nama ?? '-' }},
                                            {{ $s->kelurahan?->kecamatan?->nama ?? '-' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('akademik.master-siswa.edit', $s->siswa_id) }}"
                                                class="btn btn-sm btn-light-warning mb-1">Edit</a>
                                            <form action="{{ route('akademik.master-siswa.destroy', $s->siswa_id) }}"
                                                method="POST" style="display:inline"
                                                onsubmit="return confirm('Yakin ingin menghapus siswa ini dari sistem?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-light-danger mb-1">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada data siswa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $siswa->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel Siswa -->
    <div class="modal fade" id="modalImportSiswa" tabindex="-1" aria-labelledby="modalImportSiswaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('akademik.master-siswa.preview-import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalImportSiswaLabel"><i class="bi bi-file-earmark-excel text-success me-2"></i>Import Data Siswa dari Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-1"></i> Pastikan format file sesuai dengan template. Jika belum memiliki template, silakan 
                            <a href="{{ route('akademik.master-siswa.download-template') }}" class="alert-link fw-bold">Download Template Excel</a> terlebih dahulu.
                        </div>
                        <div class="mb-3">
                            <label for="file_excel" class="form-label fw-bold">Pilih File Excel (.xlsx / .xls)</label>
                            <input type="file" class="form-control" id="file_excel" name="file_excel" accept=".xlsx, .xls" required>
                            <div class="form-text">Maksimal ukuran file: 5MB. Data akan ditampilkan terlebih dahulu untuk Anda periksa sebelum disimpan.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-eye me-1"></i> Unggah & Pratinjau Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
