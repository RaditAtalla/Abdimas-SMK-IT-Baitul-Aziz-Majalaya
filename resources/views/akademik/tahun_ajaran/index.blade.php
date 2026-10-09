@extends('layouts.master')

@section('title', 'Tahun Ajaran')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')
    <x-breadcrumb item="Manajemen Kelas" active="Tahun Ajaran" />

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between"">
                    <div>
                        <h5>Daftar Tahun Ajaran</h5>
                    </div>
                    <div class="">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                            data-bs-target="#modalCreateTahunAjaran">
                            Tambah Tahun Ajaran
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tahun</th>
                                <th>Semester</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tahunAjaran as $ta)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $ta->tahun }}</td>
                                    <td>{{ $ta->semester }}</td>
                                    <td>
                                        <button type="button" class="btn btn-light-warning btn-sm btn-edit-ta"
                                            data-id="{{ $ta->tahun_ajaran_id }}" data-tahun="{{ $ta->tahun }}"
                                            data-semester="{{ $ta->semester }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('akademik.tahun_ajaran.destroy', $ta->tahun_ajaran_id) }}"
                                            method="POST" class="d-inline form-delete-ta">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light-danger btn-sm">Hapus</button>
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

    <!-- Modal Create/Edit Tahun Ajaran -->
    <div class="modal fade" id="modalCreateTahunAjaran" tabindex="-1" aria-labelledby="modalCreateTahunAjaranLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formCreateTahunAjaran" method="POST" action="{{ route('akademik.tahun_ajaran.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="tahunAjaranMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCreateTahunAjaranLabel">Tambah Tahun Ajaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <input type="text" class="form-control @error('tahun') is-invalid @enderror" id="tahun"
                                name="tahun" value="{{ old('tahun') }}" placeholder="2025/2026" required>
                            @error('tahun')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-select @error('semester') is-invalid @enderror" id="semester"
                                name="semester" required>
                                <option value="">Pilih Semester</option>
                                <option value="Ganjil" {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>
                                    Ganjil</option>
                                <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>
                                    Genap</option>
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanTahunAjaran">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        import {
            DataTable
        } from '/build/js/plugins/module.js';
        // Inisialisasi DataTable jika ingin
        window.dt = new DataTable('#pc-dt-simple');
    </script>
    <script>
        // Jika ada error validasi, buka modal otomatis
        @if ($errors->any())
            var myModal = new bootstrap.Modal(document.getElementById('modalCreateTahunAjaran'));
            window.addEventListener('DOMContentLoaded', function() {
                myModal.show();
            });
        @endif

        // Modal edit
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('btn-edit-ta')) {
                const btn = e.target;
                const id = btn.getAttribute('data-id');
                const tahun = btn.getAttribute('data-tahun');
                const semester = btn.getAttribute('data-semester');
                const modal = new bootstrap.Modal(document.getElementById('modalCreateTahunAjaran'));
                // Set form action & method
                const form = document.getElementById('formCreateTahunAjaran');
                form.action = "{{ url('akademik/tahun_ajaran') }}/" + id;
                document.getElementById('tahunAjaranMethod').value = 'PUT';
                // Set value
                document.getElementById('tahun').value = tahun;
                document.getElementById('semester').value = semester;
                // Set modal title
                document.getElementById('modalCreateTahunAjaranLabel').textContent = 'Edit Tahun Ajaran';
                modal.show();
            }
        });

        // Modal tambah (reset ke mode tambah)
        document.querySelector('[data-bs-target=\"#modalCreateTahunAjaran\"]').addEventListener('click', function() {
            const form = document.getElementById('formCreateTahunAjaran');
            form.action = "{{ route('akademik.tahun_ajaran.store') }}";
            document.getElementById('tahunAjaranMethod').value = 'POST';
            document.getElementById('tahun').value = '';
            document.getElementById('semester').value = '';
            document.getElementById('modalCreateTahunAjaranLabel').textContent = 'Tambah Tahun Ajaran';
        });

        // Konfirmasi hapus (robust, support dynamic & static)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.form-delete-ta').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!window.confirm('Yakin ingin menghapus tahun ajaran ini?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endsection
