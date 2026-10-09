@extends('layouts.master')

@section('title', 'Manage Siswa Ekstrakurikuler')

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
    <x-breadcrumb item="Ekstrakurikuler" subItem="Ekstrakurikuler" subLink="{{ route('ekstrakurikuler.index') }}" active="Kelola Siswa" />

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>Kelola Siswa Ekstrakurikuler: {{ $ekskul->nama_pelajaran }} ({{ $ekskul->tahunAjaran->tahun }}
                {{ $ekskul->tahunAjaran->semester }})</h5>
            <div>
                <a href="{{ route('ekstrakurikuler.manage-siswa.show-load-siswa', $ekskul->ekstrakurikuler_id) }}"
                    class="btn btn-info mb-3">
                    Load Siswa dari Kelas Lain
                </a>
                <button type="button" class="btn btn-primary mb-3 ms-2" data-bs-toggle="modal" data-bs-target="#modalLoadSiswa">
                    <i class="bi bi-plus-lg"></i> Tambah Siswa
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

            <div class="mb-3">
                <label for="siswa_id">Pilih Siswa</label>
                <div class="table-responsive" style="max-height:400px;">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>NIS</th>
                                <th>Kelas Terakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($siswaEkskul as $item)
                                @php
                                    $nama_kelas_terakhir =
                                        $item->siswa->riwayatKelasTerakhir->kelasAjar->kelas->nama_kelas ?? '-';

                                    $tahun_ajaran_terakhir =
                                        $item->siswa->riwayatKelasTerakhir->kelasAjar->tahunAjaran->tahun;

                                    $semester_terakhir =
                                        $item->siswa->riwayatKelasTerakhir->kelasAjar->tahunAjaran->semester;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->siswa->nama ?? '-' }}</td>
                                    <td>{{ $item->siswa->nis ?? '-' }}</td>
                                    <td>{{ $nama_kelas_terakhir . ' (' . $tahun_ajaran_terakhir . ' ' . $semester_terakhir . ')' }}
                                    </td>
                                    <td>
                                        <form method="POST"
                                            action="{{ route('ekstrakurikuler.manage-siswa.destroy', [$ekskul->ekstrakurikuler_id, $item->siswa_ekstrakurikuler_id]) }}"
                                            class="d-inline delete-siswa-form">
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
                @error('siswa_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <a href="{{ route('ekstrakurikuler.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    {{-- Modal Load Siswa --}}
    <div class="modal fade" id="modalLoadSiswa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formLoadSiswa" method="POST"
                    action="{{ route('ekstrakurikuler.manage-siswa.add-existing', $ekskul->ekstrakurikuler_id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Load Siswa ke Ekstrakurikuler</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="selectSiswa" class="form-label">Cari Siswa (Nama, Kelas, Tahun, NIS/NISN)</label>
                            <select id="selectSiswa" name="siswa_id" class="form-select" style="width:100%"
                                required></select>
                            @error('siswa_id')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                            <div id="infoSiswaTerpilih" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambahkan</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-siswa-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (confirm('Yakin ingin menghapus siswa dari ekskul ini?')) {
                        form.submit();
                    }
                });
            });
        });
    </script>

    <script src="/build/js/plugins/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectSiswa = document.getElementById('selectSiswa');
            if (selectSiswa) {
                const siswaChoices = new Choices(selectSiswa, {
                    searchEnabled: true,
                    placeholder: true,
                    placeholderValue: 'Cari siswa...',
                    shouldSort: false,
                    itemSelectText: '',
                    searchResultLimit: 15,
                    renderChoiceLimit: 15
                });

                let debounceTimeout;
                selectSiswa.addEventListener('search', function(event) {
                    clearTimeout(debounceTimeout);
                    const q = event.detail.value;
                    if (!q || q.length < 2) return;
                    debounceTimeout = setTimeout(function() {
                        fetch("{{ route('ekstrakurikuler.ajax.search-siswa', $ekskul->ekstrakurikuler_id) }}?q=" +
                                encodeURIComponent(q))
                            .then(res => res.json())
                            .then(data => {
                                siswaChoices.setChoices(data.results, 'id', 'text', true);
                            });
                    }, 300);
                });

                selectSiswa.addEventListener('change', function() {
                    const label = siswaChoices.getValue().length ? siswaChoices.getValue()[0].label : '';
                    document.getElementById('infoSiswaTerpilih').textContent = label ? 'Terpilih: ' +
                        label : '';
                });
            }
        });
    </script>
@endsection
