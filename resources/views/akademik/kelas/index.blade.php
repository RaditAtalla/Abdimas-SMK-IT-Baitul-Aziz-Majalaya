@extends('layouts.master')

@section('title', 'Manajemen Kelas Ajar')

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

        body[data-pc-theme="dark"] .choices__item--selectable,
        body[data-pc-theme="dark"] .choices__list--single .choices__item {
            color: rgba(255, 255, 255, .92) !important;
        }

        body[data-pc-theme="dark"] select.is-invalid+.choices .choices__inner {
            border-color: #dc3545 !important;
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb item="Manajemen Kelas" active="Manajemen Kelas" />

    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Kelas Ajar</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#modalCreateKelasAjar">
                        Tambah Kelas Ajar
                    </button>
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
                                    <th>Nama Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Semester</th>
                                    <th>Wali Kelas</th>
                                    <th>KKM</th>
                                    <th>Jumlah Siswa</th>
                                    <th>Kelola Siswa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kelasAjar as $ka)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $ka->kelas->nama_kelas ?? '-' }}</td>
                                        <td>{{ $ka->tahunAjaran->tahun ?? '-' }}</td>
                                        <td>{{ $ka->tahunAjaran->semester ?? '-' }}</td>
                                        <td>{{ $ka->waliKelas->name ?? '-' }}</td>
                                        <td>{{ (int) ($ka->kkm ?? 75) }}</td>
                                        <td>{{ $ka->riwayat_kelas_count ?? 0 }}</td>
                                        <td>
                                            <a href="{{ route('akademik.siswa.index', $ka->kelas_ajar_id) }}"
                                                class="btn btn-sm btn-primary">
                                                Manage Siswa
                                            </a>
                                        </td>
                                        <td class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-light-warning btn-edit-kelas"
                                                data-id="{{ $ka->kelas_ajar_id }}"
                                                data-nama_kelas="{{ $ka->kelas->nama_kelas ?? '' }}"
                                                data-tahun_ajaran_id="{{ $ka->tahunAjaran->tahun_ajaran_id ?? '' }}"
                                                data-wali_user_id="{{ $ka->waliKelas->id ?? '' }}"
                                                data-kkm="{{ (int) ($ka->kkm ?? 75) }}">
                                                Edit
                                            </button>

                                            <form action="{{ route('akademik.kelas.destroy', $ka->kelas_ajar_id) }}"
                                                method="POST" class="d-inline form-delete-kelas">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light-danger">Hapus</button>
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

    <!-- Modal Create/Edit Kelas Ajar -->
    <div class="modal fade" id="modalCreateKelasAjar" tabindex="-1" aria-labelledby="modalCreateKelasAjarLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form id="formCreateKelasAjar" method="POST" action="{{ route('akademik.kelas.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="kelasAjarMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCreateKelasAjarLabel">Tambah Kelas Ajar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label">Nama Kelas</label>
                            <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror"
                                id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" required>
                            @error('nama_kelas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kkm" class="form-label">KKM</label>
                            <input type="number" class="form-control @error('kkm') is-invalid @enderror" id="kkm"
                                name="kkm" value="{{ old('kkm', 75) }}" min="0" max="100" required>
                            @error('kkm')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select class="form-select @error('tahun_ajaran_id') is-invalid @enderror" id="tahun_ajaran_id"
                                name="tahun_ajaran_id" required>
                                <option value="">Pilih Tahun Ajaran</option>
                                @foreach (\App\Models\TahunAjaran::orderByDesc('tahun_ajaran_id')->get() as $ta)
                                    <option value="{{ $ta->tahun_ajaran_id }}"
                                        {{ old('tahun_ajaran_id') == $ta->tahun_ajaran_id ? 'selected' : '' }}>
                                        {{ $ta->tahun }} ({{ $ta->semester }})
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_ajaran_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="wali_user_id" class="form-label">Wali Kelas</label>
                            <select class="form-select @error('wali_user_id') is-invalid @enderror" id="wali_user_id"
                                name="wali_user_id" required>
                                <option value="">Pilih Wali Kelas</option>
                                @foreach (\App\Models\User::role('Wali Kelas')->get() as $wali)
                                    <option value="{{ $wali->id }}"
                                        {{ old('wali_user_id') == $wali->id ? 'selected' : '' }}>
                                        {{ $wali->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('wali_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
        // Jika ada error validasi, buka modal otomatis
        @if ($errors->any())
            window.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('modalCreateKelasAjar');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });
        @endif

        // Confirm delete
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.form-delete-kelas').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!window.confirm('Yakin ingin menghapus kelas ajar ini?')) e
                        .preventDefault();
                });
            });
        });

        // Modal tambah (reset ke mode tambah)
        document.addEventListener('click', function(e) {
            const btnAdd = e.target.closest('[data-bs-target="#modalCreateKelasAjar"]');
            if (!btnAdd) return;

            const form = document.getElementById('formCreateKelasAjar');
            form.action = "{{ route('akademik.kelas.store') }}";
            document.getElementById('kelasAjarMethod').value = 'POST';

            document.getElementById('nama_kelas').value = '';
            document.getElementById('kkm').value = 75;

            // reset select
            document.getElementById('tahun_ajaran_id').value = '';
            document.getElementById('wali_user_id').value = '';

            // kalau pakai Choices, reset juga UI nya
            if (window.choicesTahun) window.choicesTahun.setChoiceByValue('');
            if (window.choicesWali) window.choicesWali.setChoiceByValue('');

            document.getElementById('modalCreateKelasAjarLabel').textContent = 'Tambah Kelas Ajar';
        });

        // Modal edit kelas (event delegation robust: pakai closest)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-edit-kelas');
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const nama_kelas = btn.getAttribute('data-nama_kelas') || '';
            const tahun_ajaran_id = btn.getAttribute('data-tahun_ajaran_id') || '';
            const wali_user_id = btn.getAttribute('data-wali_user_id') || '';
            const kkm = btn.getAttribute('data-kkm');

            const form = document.getElementById('formCreateKelasAjar');
            form.action = "{{ url('akademik/kelas') }}/" + id;
            document.getElementById('kelasAjarMethod').value = 'PUT';

            document.getElementById('nama_kelas').value = nama_kelas;
            document.getElementById('kkm').value = (kkm !== null && kkm !== '') ? kkm : 75;

            document.getElementById('tahun_ajaran_id').value = tahun_ajaran_id;
            document.getElementById('wali_user_id').value = wali_user_id;

            // kalau pakai Choices, set juga UI nya biar sinkron
            if (window.choicesTahun && tahun_ajaran_id !== '') {
                window.choicesTahun.setChoiceByValue(String(tahun_ajaran_id));
            }
            if (window.choicesWali && wali_user_id !== '') {
                window.choicesWali.setChoiceByValue(String(wali_user_id));
            }

            document.getElementById('modalCreateKelasAjarLabel').textContent = 'Edit Kelas Ajar';

            const modalEl = document.getElementById('modalCreateKelasAjar');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    </script>

    <script src="/build/js/plugins/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simpan instance choices biar bisa di-set/reset saat edit/tambah
            window.choicesTahun = new Choices('#tahun_ajaran_id', {
                searchEnabled: true,
                placeholder: true,
                itemSelectText: '',
                shouldSort: false,
                searchResultLimit: 15,
                renderChoiceLimit: 15
            });

            window.choicesWali = new Choices('#wali_user_id', {
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
