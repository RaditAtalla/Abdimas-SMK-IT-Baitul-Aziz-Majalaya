@extends('layouts.master')

@section('title', 'Intrakurikuler')

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
    <x-breadcrumb item="Intrakurikuler" active="Intrakurikuler" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div
                    class="card-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="mb-0">Daftar Mata Pelajaran Intrakurikuler</h5>
                        {{-- <span class="d-block m-t-5">
                            Tahun Ajaran
                            {{ $intrakurikuler->first()?->kelasAjar?->tahunAjaran?->tahun ?? '-' }}
                            {{ $intrakurikuler->first()?->kelasAjar?->tahunAjaran?->semester ?? '' }}
                        </span> --}}
                    </div>

                    @role('Bagian Akademik|Super Admin')
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalIntra"
                                data-mode="create" data-id="" data-nama="" data-kelas="" data-guru="">
                                <i class="bi bi-plus-lg"></i> Tambah Intrakurikuler
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
                                    <th>Mata Pelajaran</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Semester</th>
                                    <th>Kelas</th>
                                    @role('Bagian Akademik|Super Admin')
                                        <th>Guru</th>
                                    @endrole
                                    <th>Jumlah Siswa</th>
                                    @role('Bagian Akademik|Super Admin')
                                        <th>Actions</th>
                                    @endrole
                                    <th>Lingkup materi</th>
                                    <th>Tujuan pembelajaran</th>
                                    <th>Import excel</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($intrakurikuler as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->nama_pelajaran }}</td>
                                        <td>
                                            {{ $item->kelasAjar?->tahunAjaran?->tahun ?? '-' }}
                                        </td>
                                        <td>
                                            {{ ucfirst($item->kelasAjar?->tahunAjaran?->semester ?? '-') }}
                                        </td>
                                        <td>{{ $item->kelasAjar?->kelas?->nama_kelas ?? '-' }}</td>
                                        @role('Bagian Akademik|Super Admin')
                                            <td>{{ $item->pengampu?->staff?->nama ?? ($item->pengampu?->name ?? 'N/A') }}</td>
                                        @endrole
                                        <td>{{ $item->kelasAjar?->riwayat_kelas_count ?? 0 }}</td>

                                        @role('Bagian Akademik|Super Admin')
                                            <td>
                                                <button type="button" class="btn btn-sm btn-light-warning mb-1"
                                                    data-bs-toggle="modal" data-bs-target="#modalIntra" data-mode="edit"
                                                    data-id="{{ $item->intrakurikuler_id }}"
                                                    data-nama="{{ e($item->nama_pelajaran) }}"
                                                    data-kelas="{{ $item->kelas_ajar_id }}"
                                                    data-guru="{{ $item->pengampu_user_id }}">
                                                    Edit
                                                </button>

                                                <form action="{{ route('intrakurikuler.destroy', $item->intrakurikuler_id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin hapus intrakurikuler ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light-danger mb-1">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endrole

                                        <td>
                                            <a href="{{ route('lingkup-materi.index', $item->intrakurikuler_id) }}"
                                                class="btn btn-sm btn-light-primary mb-1">Lingkup materi</a>
                                            <a href="{{ route('assesment-sumatif.index', $item->intrakurikuler_id) }}"
                                                class="btn btn-sm btn-light-primary mb-1">Asesmen Sumatif</a>
                                        </td>

                                        <td>
                                            <a href="{{ route('tujuan-pembelajaran.index', $item->intrakurikuler_id) }}"
                                                class="btn btn-sm btn-light-primary mb-1">Tujuan pembelajaran</a>
                                            <a href="{{ route('assesment-formatif.index', $item->intrakurikuler_id) }}"
                                                class="btn btn-sm btn-light-primary mb-1">Asesmen Formatif</a>
                                        </td>
                                        {{-- new feature --}}
                                        <td class="d-flex align-items-center gap-2">
                                            <a href="{{ route('template_tp_lm.template', $item->intrakurikuler_id) }}" class="btn btn-primary">
                                                <i class="bi bi-download"></i> Unduh Template Excel
                                            </a>
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#modalImportExcel" data-id="{{ $item->intrakurikuler_id }}">
                                                <i class="bi bi-save"></i> Masukkan Data Excel
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada data intrakurikuler.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL CREATE / EDIT INTRAKURIKULER ===================== --}}
    <div class="modal fade" id="modalIntra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form id="intraForm" method="POST" action="{{ route('intrakurikuler.store') }}">
                    @csrf
                    <div id="intraMethodSpoof"></div>

                    {{-- marker untuk auto-open modal saat validation error --}}
                    <input type="hidden" name="_modal_mode" id="intra_modal_mode"
                        value="{{ old('_modal_mode', 'create') }}">
                    <input type="hidden" name="_edit_id" id="intra_edit_id" value="{{ old('_edit_id') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="intraModalTitle">Tambah Intrakurikuler</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Mata Pelajaran</label>
                            <input type="text" name="nama_pelajaran" id="intra_nama" value="{{ old('nama_pelajaran') }}"
                                class="form-control @error('nama_pelajaran') is-invalid @enderror">
                            @error('nama_pelajaran')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Kelas Ajar</label>
                            <select name="kelas_ajar_id" id="intra_kelas"
                                class="form-control @error('kelas_ajar_id') is-invalid @enderror" required>
                                <option value="">Pilih Kelas Ajar</option>
                                @foreach ($kelasAjar as $ka)
                                    <option value="{{ $ka->kelas_ajar_id }}"
                                        {{ old('kelas_ajar_id') == $ka->kelas_ajar_id ? 'selected' : '' }}>
                                        {{ $ka->kelas->nama_kelas }} • {{ $ka->tahunAjaran->tahun }}
                                        {{ $ka->tahunAjaran->semester }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_ajar_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Guru Pengampu</label>
                            <select name="pengampu_user_id" id="intra_guru"
                                class="form-control @error('pengampu_user_id') is-invalid @enderror" required>
                                <option value="">Pilih Guru</option>
                                @foreach ($guru as $g)
                                    <option value="{{ $g->id }}"
                                        {{ old('pengampu_user_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->staff?->nama ?? $g->name }}
                                        @if ($g->staff?->nuptk)
                                            - NUPTK: {{ $g->staff->nuptk }}
                                        @endif
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

    {{-- ===================== MODAL MASUKKAN DATA EXCEL ===================== --}}
    <div class="modal fade" id="modalImportExcel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form id="excelForm" method="POST" action="{{ route('template_tp_lm.import', $item->intrakurikuler_id) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="excelModalTitle">Masukkan Data Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label d-block">Opsi Import</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_tambah" value="tambah" checked>
                                <label class="form-check-label" for="mode_tambah">Tambah Data</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_timpa" value="timpa">
                                <label class="form-check-label" for="mode_timpa">Timpa Data</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Excel</label>
                            <input type="file" name="excel" id="excel_file"
                                class="form-control @error('excel') is-invalid @enderror" accept=".xlsx, .xls" required>
                            @error('excel')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <div class="form-text text-muted">
                                Unggah file Excel (.xlsx / .xls) sesuai dengan template yang telah disediakan.
                            </div>
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
        const modalEl = document.getElementById('modalIntra');

        const form = document.getElementById('intraForm');
        const methodSpoof = document.getElementById('intraMethodSpoof');
        const titleEl = document.getElementById('intraModalTitle');

        const inputNama = document.getElementById('intra_nama');
        const selectKelas = document.getElementById('intra_kelas');
        const selectGuru = document.getElementById('intra_guru');

        const modeInput = document.getElementById('intra_modal_mode');
        const editIdInput = document.getElementById('intra_edit_id');

        const storeUrl = {!! json_encode(route('intrakurikuler.store')) !!};
        const updateUrlTemplate = {!! json_encode(route('intrakurikuler.update', ['intrakurikuler' => '___ID___'])) !!};

        function setCreateMode() {
            titleEl.textContent = 'Tambah Intrakurikuler';
            form.action = storeUrl;
            methodSpoof.innerHTML = '';
            modeInput.value = 'create';
            editIdInput.value = '';

            // reset field (kalau bukan dari old())
            if (!@json(old('nama_pelajaran'))) inputNama.value = '';
            if (!@json(old('kelas_ajar_id'))) selectKelas.value = '';
            if (!@json(old('pengampu_user_id'))) selectGuru.value = '';
        }

        function setEditMode(id, nama, kelas, guru) {
            titleEl.textContent = 'Edit Intrakurikuler';
            form.action = updateUrlTemplate.replace('___ID___', id);
            methodSpoof.innerHTML = '<input type="hidden" name="_method" value="PUT">'
            modeInput.value = 'edit';
            editIdInput.value = id;

            // isi field dari dataset (kecuali kalau sedang old() dari validation error)
            if (!@json(old('nama_pelajaran'))) inputNama.value = nama || '';
            if (!@json(old('kelas_ajar_id'))) selectKelas.value = kelas || '';
            if (!@json(old('pengampu_user_id'))) selectGuru.value = guru || '';
        }

        modalEl.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            const mode = btn?.getAttribute('data-mode') || 'create';

            if (mode === 'edit') {
                setEditMode(
                    btn.getAttribute('data-id'),
                    btn.getAttribute('data-nama'),
                    btn.getAttribute('data-kelas'),
                    btn.getAttribute('data-guru'),
                );
            } else {
                setCreateMode();
            }
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            // reset total supaya aman
            inputNama.value = '';
            selectKelas.value = '';
            selectGuru.value = '';
            methodSpoof.innerHTML = '';
            form.action = storeUrl;
            titleEl.textContent = 'Tambah Intrakurikuler';
            modeInput.value = 'create';
            editIdInput.value = '';
        });

        // auto open modal kalau validasi error, mode disesuaikan
        @if ($errors->any())
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();

            // kalau error dari edit, set action ke update + method PUT
            @if (old('_modal_mode') === 'edit' && old('_edit_id'))
                setEditMode(
                    {!! json_encode(old('_edit_id')) !!},
                    {!! json_encode(old('nama_pelajaran')) !!},
                    {!! json_encode(old('kelas_ajar_id')) !!},
                    {!! json_encode(old('pengampu_user_id')) !!}
                );
            @else
                setCreateMode();
            @endif
        @endif

        // Modal Masukkan Data Excel
        const excelModalEl = document.getElementById('modalImportExcel');
        const excelForm = document.getElementById('excelForm');
        const importUrlTemplate = {!! json_encode(route('template_tp_lm.import', ['intrakurikuler' => '___ID___'])) !!};

        if (excelModalEl) {
            excelModalEl.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                const id = btn ? btn.getAttribute('data-id') : '';
                if (id && excelForm) {
                    excelForm.action = importUrlTemplate.replace('___ID___', id);
                }
            });
        }
    </script>

    <script src="/build/js/plugins/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#intra_kelas', {
                searchEnabled: true,
                placeholder: true,
                itemSelectText: '',
                shouldSort: false,
                searchResultLimit: 15,
                renderChoiceLimit: 15
            });
            new Choices('#intra_guru', {
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
