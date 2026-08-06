@extends('layouts.master')

@section('title', 'Load Siswa dari Kelas Lain')

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
    <x-breadcrumb item="Ekstrakurikuler" subItem="Ekstrakurikuler" subLink="{{ route('ekstrakurikuler.index') }}" sub2Item="Kelola Siswa" sub2Link="{{ route('ekstrakurikuler.manage-siswa.index', $ekskul->ekstrakurikuler_id) }}" active="Ambil Data Siswa" />

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5>Load Siswa ke Ekstrakurikuler {{ $ekskul->nama_pelajaran }} ({{ $ekskul->tahunAjaran->tahun }}
                    {{ $ekskul->tahunAjaran->semester }})</h5>
            </div>
            <div>
                <a href="{{ route('ekstrakurikuler.manage-siswa.index', $ekskul->ekstrakurikuler_id) }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger mt-4">{{ session('error') }}</div>
            @endif
            @error('kelas_asal_id')
                <div class="mt-4 alert alert-danger">
                    {{ $message }}
                </div>
            @enderror

            <form method="GET" action="">
                <div class="form-check mb-2">
                    <input class="form-check-input check-filter" type="checkbox" id="showOtherSemester" name="show_other_semester" value="true" {{ request('show_other_semester') == 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="showOtherSemester">
                        Tampilkan kelas dari semua semester
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input check-filter" type="checkbox" id="showOtherTahun" name="show_other_tahun" value="true" {{ request('show_other_tahun') == 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="showOtherTahun">
                        Tampilkan kelas dari semua tahun ajaran
                    </label>
                </div>
                <div class="mb-3">
                    <label for="kelas_asal_id" class="form-label">Pilih Kelas Asal</label>
                    <select id="kelas_asal_id" name="kelas_asal_id" class="form-select" required></select>
                </div>
                <button type="submit" class="btn btn-primary">Tampilkan Siswa</button>
            </form>

            @if ($kelasAsalId && count($siswaList))
                <div class="alert alert-info mt-4">
                    <strong>Kelas Asal:</strong>
                    {{ $kelasAsal->kelas->nama_kelas ?? '-' }}
                    ({{ $kelasAsal->tahunAjaran->tahun ?? '-' }} {{ $kelasAsal->tahunAjaran->semester ?? '-' }})
                </div>
                <form method="POST" action="{{ route('ekstrakurikuler.manage-siswa.load-siswa', $ekskul->ekstrakurikuler_id) }}">
                    @csrf
                    <input type="hidden" name="kelas_asal_id" value="{{ $kelasAsalId }}">
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="checkAll" checked></th>
                                    <th>Nama</th>
                                    <th>NIS</th>
                                    <th>NISN</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Alamat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($siswaList as $siswa)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->siswa_id }}"
                                                checked>
                                        </td>
                                        <td>{{ $siswa->user->name ?? $siswa->nama }}</td>
                                        <td>{{ $siswa->nis }}</td>
                                        <td>{{ $siswa->nisn }}</td>
                                        <td>{{ $siswa->jenis_kelamin == 'l' ? 'Laki-laki' : 'Perempuan' }}</td>
                                        <td>{{ $siswa->alamat }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success mt-2">Load Siswa Terpilih</button>
                </form>
            @elseif($kelasAsalId)
                <div class="alert alert-warning mt-4">Tidak ada siswa di kelas asal yang dipilih atau semua siswa sudah terdaftar di ekstrakurikuler ini.</div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/build/js/plugins/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Choices.js untuk kelas asal
            const kelasSelect = document.getElementById('kelas_asal_id');
            if (kelasSelect) {
                const instance = new Choices(kelasSelect, {
                    searchEnabled: true,
                    placeholder: true,
                    placeholderValue: 'Cari kelas...',
                    shouldSort: false,
                    itemSelectText: '',
                    searchResultLimit: 15,
                    renderChoiceLimit: 15
                });

                kelasSelect.addEventListener('search', function(event) {
                    const q = event.detail.value;
                    const showOtherSemester = document.getElementById('showOtherSemester').checked;
                    const showOtherTahun = document.getElementById('showOtherTahun').checked;
                    if (!q || q.length < 2) return;
                    fetch("{{ route('ekstrakurikuler.ajax.kelas.search', $ekskul->ekstrakurikuler_id) }}?show_other_semester=" + showOtherSemester + "&show_other_tahun=" + showOtherTahun + "&q=" + encodeURIComponent(q))
                        .then(res => res.json())
                        .then(data => {
                            instance.setChoices(data.results, 'id', 'text', true);
                        });
                });

                const checkFilters = document.querySelectorAll('.check-filter');
                checkFilters.forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        instance.clearChoices();
                        instance.clearInput();
                    });
                });

                // Set selected jika sudah ada
                @if ($kelasAsalId)
                    instance.setChoiceByValue('{{ $kelasAsalId }}');
                @endif
            }

            // Checkbox all
            const checkAll = document.getElementById('checkAll');
            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    document.querySelectorAll('input[name="siswa_ids[]"]').forEach(cb => {
                        cb.checked = checkAll.checked;
                    });
                });
            }
        });
    </script>
@endsection
