@extends('layouts.master')

@section('title', 'Tujuan Pembelajaran')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')

    <x-breadcrumb item="Intrakurikuler" subItem="Intrakurikuler" subLink="{{ route('intrakurikuler.index') }}" active="Tujuan Pembelajaran" />

    <div class="row">
        <!-- [ basic-table ] start -->
        <div class="col-xl-12">
            <div class="card">

                {{-- Header --}}
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">{{ $intrakurikuler->nama_pelajaran }}</h5>
                        <span class="d-block m-t-5">
                            {{ $intrakurikuler->kelasAjar->kelas->nama_kelas ?? '-' }}
                            • {{ $intrakurikuler->kelasAjar->tahunAjaran->tahun ?? '-' }}
                            {{ $intrakurikuler->kelasAjar->tahunAjaran->semester ?? '' }}
                        </span>
                    </div>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#tujuanPembelajaranModal" data-mode="create" data-title="Tambah Tujuan Pembelajaran"
                        data-id="" data-nama="">
                        Tambah Tujuan Pembelajaran
                    </button>
                </div>

                {{-- Body --}}
                <div class="card-body table-border-style">
                    @if (session('success'))
                        <div class="alert alert-success mt-4">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mt-4">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama materi</th>
                                    <th style="width: 180px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tujuanPembelajaran as $tp)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $tp->deskripsi }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-light-warning"
                                                data-bs-toggle="modal" data-bs-target="#tujuanPembelajaranModal"
                                                data-mode="edit" data-title="Edit Tujuan Pembelajaran"
                                                data-id="{{ $tp->tujuan_pembelajaran_id }}"
                                                data-deskripsi="{{ $tp->deskripsi }}">
                                                Edit
                                            </button>
                                            <form
                                                action="{{ route('tujuan-pembelajaran.destroy', [$intrakurikuler->intrakurikuler_id, $tp->tujuan_pembelajaran_id]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus tujuan pembelajaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada tujuan pembelajaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <a href="{{ route('intrakurikuler.index') }}" class="btn btn-light-secondary">Kembali</a>
                </div>
            </div>
        </div>
        <!-- [ basic-table ] end -->
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="tujuanPembelajaranModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="tujuanPembelajaranForm" method="POST">
                    @csrf
                    <div id="methodSpoof"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="tujuanPembelajaranModalTitle">Tambah Tujuan Pembelajaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="tp_id">
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" class="form-control" name="deskripsi" id="tp_deskripsi" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
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
        const modalEl = document.getElementById('tujuanPembelajaranModal');
        modalEl.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            const mode = btn.getAttribute('data-mode') || 'create';
            const title = btn.getAttribute('data-title') || 'Tambah Tujuan Pembelajaran';
            const id = btn.getAttribute('data-id') || '';
            const deskripsi = btn.getAttribute('data-deskripsi') || '';

            document.getElementById('tujuanPembelajaranModalTitle').textContent = title;
            document.getElementById('tp_id').value = id;
            document.getElementById('tp_deskripsi').value = deskripsi;

            const form = document.getElementById('tujuanPembelajaranForm');
            const methodSpoof = document.getElementById('methodSpoof');
            methodSpoof.innerHTML = '';

            if (mode === 'edit' && id) {
                form.action = "{{ url()->current() }}/" + id;
                methodSpoof.innerHTML = '@method('PUT')';
            } else {
                form.action = "{{ url()->current() }}";
            }
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            document.getElementById('tp_id').value = '';
            document.getElementById('tp_deskripsi').value = '';
            document.getElementById('methodSpoof').innerHTML = '';
            document.getElementById('tujuanPembelajaranForm').action = "{{ url()->current() }}";
            document.getElementById('tujuanPembelajaranModalTitle').textContent = 'Tambah Tujuan Pembelajaran';
        });
    </script>
@endsection
