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
    <x-breadcrumb item="Manajemen Siswa" active="Load Siswa" />

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5>Load Siswa ke {{ $kelasTujuan->kelas->nama_kelas }} ({{ $kelasTujuan->tahunAjaran->tahun }}
                    {{ $kelasTujuan->tahunAjaran->semester }})</h5>
            </div>
            <div>
                <a href="{{ route('akademik.siswa.index', $kelasTujuan->kelas_ajar_id) }}" class="btn btn-secondary">Kembali</a>
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
                <form method="POST" action="{{ route('akademik.kelas.load-siswa', $kelasTujuan->kelas_ajar_id) }}">
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
                <div class="alert alert-warning mt-4">Tidak ada siswa di kelas asal yang dipilih.</div>
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
                    if (!q || q.length < 2) return;
                    fetch("{{ route('akademik.ajax.kelas.search') }}?q=" + encodeURIComponent(q))
                        .then(res => res.json())
                        .then(data => {
                            instance.setChoices(data.results, 'id', 'text', true);
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
