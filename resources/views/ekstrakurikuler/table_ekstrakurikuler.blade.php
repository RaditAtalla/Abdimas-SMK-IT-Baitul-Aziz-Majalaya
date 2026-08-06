@extends('layouts.master')

@section('title', 'Ekstrakurikuler')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />

    <style>
        /* ===== Choices DARK MODE FIX (Able Pro uses body[data-pc-theme="dark"]) ===== */
        body[data-pc-theme="dark"] .choices__inner {
            background-color: rgba(255, 255, 255, .06) !important;
            border-color: rgba(255, 255, 255, .18) !important;
            color: rgba(255, 255, 255, .90) !important;
        }

        body[data-pc-theme="dark"] .choices__input {
            background-color: transparent !important;
            color: rgba(255, 255, 255, .92) !important;
        }

        body[data-pc-theme="dark"] .choices__input::placeholder {
            color: rgba(255, 255, 255, .55) !important;
        }

        body[data-pc-theme="dark"] .choices__list--dropdown,
        body[data-pc-theme="dark"] .choices__list[aria-expanded] {
            background-color: #1b1f24 !important;
            border-color: rgba(255, 255, 255, .14) !important;
            color: rgba(255, 255, 255, .92) !important;
        }

        body[data-pc-theme="dark"] .choices__list--dropdown .choices__item {
            color: rgba(255, 255, 255, .92) !important;
        }

        body[data-pc-theme="dark"] .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: rgba(255, 255, 255, .08) !important;
        }

        body[data-pc-theme="dark"] .choices__item--selectable {
            color: rgba(255, 255, 255, .92) !important;
        }

        /* selected item chip (kalau single select, ini text yang tampil) */
        body[data-pc-theme="dark"] .choices__item--selectable,
        body[data-pc-theme="dark"] .choices__list--single .choices__item {
            color: rgba(255, 255, 255, .92) !important;
        }

        /* kalau invalid, tetap merah */
        body[data-pc-theme="dark"] select.is-invalid+.choices .choices__inner {
            border-color: #dc3545 !important;
        }
    </style>
@endsection

@section('content')

    <x-breadcrumb item="Ekstrakurikuler" active="Ekstrakurikuler" />

    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Daftar Ekstrakurikuler</h5>
                        <span class="d-span m-t-5">Tahun Ajaran</span>
                        {{ $ekstrakurikuler->first()?->tahunAjaran?->tahun ?? '-' }}
                        {{ $ekstrakurikuler->first()?->tahunAjaran?->semester ?? '' }}
                    </div>

                    @role('Bagian Akademik|Super Admin')
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEkstra"
                                data-mode="create" data-id="" data-nama="" data-tahun="" data-guru="">
                                <i class="bi bi-plus-lg"></i> Tambah Ekstrakurikuler
                            </button>
                        </div>
                    @endrole
                </div>
                <div class="card-body table-border-style">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Ekstrakurikuler</th>
                                    <th>Tahun Ajaran</th>
                                    @role('Bagain Akademik|Super Admin')
                                        <th>Guru</th>
                                    @endrole
                                    <th>Jumlah Siswa</th>
                                    @role('Bagain Akademik|Super Admin')
                                        <th>Aksi</th>
                                    @endrole
                                    <th>Akademik</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ekstrakurikuler as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->nama_pelajaran }}</td>
                                        <td>
                                            {{ $item->tahunAjaran?->tahun ?? '-' }}
                                            {{ $item->tahunAjaran?->semester ?? '' }}
                                        </td>
                                        @role('Bagain Akademik|Super Admin')
                                            <td>{{ $item->pembina?->name ?? '-' }}</td>
                                        @endrole
                                        <td>{{ $item->peserta_count ?? '-' }}</td>
                                        @role('Bagain Akademik|Super Admin')
                                            <td>
                                                <button type="button" class="btn btn-sm btn-light-warning mb-1"
                                                    data-bs-toggle="modal" data-bs-target="#modalEkstra" data-mode="edit"
                                                    data-id="{{ $item->ekstrakurikuler_id }}"
                                                    data-nama="{{ $item->nama_pelajaran }}"
                                                    data-tahun="{{ $item->tahun_ajaran_id }}"
                                                    data-guru="{{ $item->user_id }}">
                                                    Edit
                                                </button>
                                                <form
                                                    action="{{ route('ekstrakurikuler.destroy', $item->ekstrakurikuler_id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus ekstrakurikuler ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-light-danger mb-1">Hapus</button>
                                                </form>
                                            </td>
                                        @endrole
                                        <td>
                                            <a href="{{ route('ekstrakurikuler.manage-siswa.index', $item->ekstrakurikuler_id) }}"
                                                class="btn btn-sm btn-light-primary mb-1">Kelola Siswa</a>
                                            <a href="{{ route('penilaian_ekstrakurikuler.index', $item->ekstrakurikuler_id) }}"
                                                class="btn btn-sm btn-light-primary mb-1">Penilaian</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data ekstrakurikuler.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    {{-- ===================== MODAL CREATE EKSTRAKURIKULER ===================== --}}
    <div class="modal fade" id="modalEkstra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form id="ekstraForm" method="POST" action="{{ route('ekstrakurikuler.store') }}">
                    @csrf
                    <div id="ekstraMethodSpoof"></div>

                    <input type="hidden" name="_modal_mode" id="ekstra_modal_mode"
                        value="{{ old('_modal_mode', 'create') }}">
                    <input type="hidden" name="_edit_id" id="ekstra_edit_id" value="{{ old('_edit_id') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="ekstraModalTitle">Tambah Ekstrakurikuler</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Nama Ekstrakurikuler</label>
                            <input type="text" name="nama_pelajaran" id="ekstra_nama"
                                value="{{ old('nama_pelajaran') }}"
                                class="form-control @error('nama_pelajaran') is-invalid @enderror" required>
                            @error('nama_pelajaran')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Tahun Ajaran</label>
                            <select name="tahun_ajaran_id" id="ekstra_tahun"
                                class="form-control @error('tahun_ajaran_id') is-invalid @enderror" required>
                                <option value="">Pilih Tahun Ajaran</option>
                                @foreach ($tahunAjaran as $ta)
                                    <option value="{{ $ta->tahun_ajaran_id }}"
                                        {{ old('tahun_ajaran_id') == $ta->tahun_ajaran_id ? 'selected' : '' }}>
                                        {{ $ta->tahun }} {{ $ta->semester }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_ajaran_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Guru Pengampu</label>
                            <select name="pengampu_user_id" id="ekstra_guru"
                                class="form-control @error('pengampu_user_id') is-invalid @enderror" required>
                                <option value="">Pilih Guru</option>
                                @foreach ($guru as $g)
                                    <option value="{{ $g->id }}"
                                        {{ old('pengampu_user_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pengampu_user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
    <script type="module">
        import {
            DataTable
        } from '/build/js/plugins/module.js';
        window.dt = new DataTable('#pc-dt-simple');
    </script>

    <script>
        const modalEl = document.getElementById('modalEkstra');
        const form = document.getElementById('ekstraForm');
        const methodSpoof = document.getElementById('ekstraMethodSpoof');
        const titleEl = document.getElementById('ekstraModalTitle');
        const inputNama = document.getElementById('ekstra_nama');
        const selectTahun = document.getElementById('ekstra_tahun');
        const selectGuru = document.getElementById('ekstra_guru');
        const modeInput = document.getElementById('ekstra_modal_mode');
        const editIdInput = document.getElementById('ekstra_edit_id');
        const storeUrl = {!! json_encode(route('ekstrakurikuler.store')) !!};
        const updateUrlTemplate = {!! json_encode(route('ekstrakurikuler.update', ['ekstrakurikuler' => '___ID___'])) !!};

        function setCreateMode() {
            titleEl.textContent = 'Tambah Ekstrakurikuler';
            form.action = storeUrl;
            methodSpoof.innerHTML = '';
            modeInput.value = 'create';
            editIdInput.value = '';
            // reset field (kecuali jika old() dari validasi)
            if (!@json(old('nama_pelajaran'))) inputNama.value = '';
            if (!@json(old('tahun_ajaran_id'))) selectTahun.value = '';
            if (!@json(old('pengampu_user_id'))) selectGuru.value = '';
        }

        function setEditMode(id, nama, tahun, guru) {
            titleEl.textContent = 'Edit Ekstrakurikuler';
            form.action = updateUrlTemplate.replace('___ID___', id);
            methodSpoof.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            modeInput.value = 'edit';
            editIdInput.value = id;
            // isi field dari data-attribute (kecuali jika old() dari validasi)
            if (!@json(old('nama_pelajaran'))) inputNama.value = nama || '';
            if (!@json(old('tahun_ajaran_id'))) selectTahun.value = tahun || '';
            if (!@json(old('pengampu_user_id'))) selectGuru.value = guru || '';
        }

        modalEl.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            const mode = btn?.getAttribute('data-mode') || 'create';
            if (mode === 'edit') {
                setEditMode(
                    btn.getAttribute('data-id'),
                    btn.getAttribute('data-nama'),
                    btn.getAttribute('data-tahun'),
                    btn.getAttribute('data-guru'),
                );
            } else {
                setCreateMode();
            }
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            inputNama.value = '';
            selectTahun.value = '';
            selectGuru.value = '';
            methodSpoof.innerHTML = '';
            form.action = storeUrl;
            titleEl.textContent = 'Tambah Ekstrakurikuler';
            modeInput.value = 'create';
            editIdInput.value = '';
        });

        // Auto open modal jika validasi error, mode disesuaikan
        @if ($errors->any())
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
            @if (old('_modal_mode') === 'edit' && old('_edit_id'))
                setEditMode(
                    {!! json_encode(old('_edit_id')) !!},
                    {!! json_encode(old('nama_pelajaran')) !!},
                    {!! json_encode(old('tahun_ajaran_id')) !!},
                    {!! json_encode(old('pengampu_user_id')) !!}
                );
            @else
                setCreateMode();
            @endif
        @endif
    </script>

    <script src="/build/js/plugins/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#ekstra_tahun', {
                searchEnabled: true,
                placeholder: true,
                itemSelectText: '',
                shouldSort: false,
                searchResultLimit: 15,
                renderChoiceLimit: 15
            });
            new Choices('#ekstra_guru', {
                searchEnabled: true,
                placeholder: true,
                itemSelectText: '',
                shouldSort: false,
                searchResultLimit: 15,
                renderChoiceLimit: 15
            });
        });
    </script>
@endsection
