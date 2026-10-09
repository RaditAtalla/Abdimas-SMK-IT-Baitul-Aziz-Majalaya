@extends('layouts.master')

@section('title', 'Manajemen Siswa')

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
    <x-breadcrumb item="Manajemen Kelas" subItem="Manajemen Kelas" subLink="{{ route('akademik.kelas.index') }}" active="Kelola Siswa" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Daftar Siswa</h5>
                        <span class="d-block m-t-5">
                            {{ $kelas_ajar->tahunAjaran->tahun ?? '-' }} {{ $kelas_ajar->tahunAjaran->semester ?? '' }}
                            • {{ $kelas_ajar->kelas->nama_kelas ?? '-' }}
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('akademik.kelas.show-load-siswa', $kelas_ajar->kelas_ajar_id) }}"
                            class="btn btn-info">
                            Load Siswa dari Kelas Lain
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddExistingSiswa">
                            <i class="bi bi-plus-lg"></i> Tambah Siswa
                        </button>
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
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @error('siswa_id')
                        <div class="alert alert-danger mb-3">{{ $message }}</div>
                    @enderror
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>NIS / NISN</th>
                                    <th>Domisili</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($riwayat as $r)
                                    @php($s = $r->siswa)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $s?->user?->name ?? ($s?->nama ?? '-') }}</td>
                                        <td>{{ $s?->user?->username ?? '-' }}</td>
                                        <td>{{ $s?->nis ?? '-' }} / {{ $s?->nisn ?? '-' }}</td>
                                        <td>
                                            {{ $s?->kelurahan?->nama ?? '-' }},
                                            {{ $s?->kelurahan?->kecamatan?->nama ?? '-' }}
                                        </td>
                                        <td>
                                            @if ($s)
                                                <a href="{{ route('akademik.siswa.edit', [$kelas_ajar->kelas_ajar_id, $s->siswa_id]) }}"
                                                    class="btn btn-sm btn-light-warning mb-1">Edit</a>
                                                <button type="button" class="btn btn-sm btn-light-info mb-1 btn-pindahkan-siswa"
                                                    data-bs-toggle="modal" data-bs-target="#modalPindahkanSiswa"
                                                    data-siswa-id="{{ $s->siswa_id }}"
                                                    data-siswa-nama="{{ $s?->user?->name ?? ($s?->nama ?? '-') }}">
                                                    Pindahkan
                                                </button>
                                                <form
                                                    action="{{ route('akademik.siswa.destroy', [$kelas_ajar->kelas_ajar_id, $s->siswa_id]) }}"
                                                    method="POST" style="display:inline"
                                                    onsubmit="return confirm('Yakin ingin mengeluarkan siswa dari kelas ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-light-danger mb-1">Keluarkan</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada data siswa di kelas ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                    <a href="{{ route('akademik.kelas.index') }}" class="btn btn-light-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalAddExistingSiswa" tabindex="-1" aria-labelledby="modalAddExistingSiswaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('akademik.kelas.add-existing-siswa', $kelas_ajar->kelas_ajar_id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAddExistingSiswaLabel">Pilih Siswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="existing_siswa_id" class="form-label">Cari Siswa</label>
                            <select id="existing_siswa_id" name="siswa_id" class="form-select" style="width:100%" required></select>
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

    <div class="modal fade" id="modalPindahkanSiswa" tabindex="-1" aria-labelledby="modalPindahkanSiswaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formPindahkanSiswa" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPindahkanSiswaLabel">Pindahkan Siswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Pindahkan <strong id="namaSiswaPindah"></strong> ke kelas:</p>
                        <div class="form-check mb-2">
                            <input class="form-check-input pindah-filter" type="checkbox" id="pindahShowOtherSemester"
                                value="true">
                            <label class="form-check-label" for="pindahShowOtherSemester">Tampilkan semua semester</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input pindah-filter" type="checkbox" id="pindahShowOtherTahun"
                                value="true">
                            <label class="form-check-label" for="pindahShowOtherTahun">Tampilkan semua tahun ajaran</label>
                        </div>
                        <label for="tujuan_kelas_ajar_id" class="form-label">Kelas Tujuan</label>
                        <select id="tujuan_kelas_ajar_id" name="tujuan_kelas_ajar_id" class="form-select"
                            style="width:100%" required></select>
                        @error('tujuan_kelas_ajar_id')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info">Pindahkan</button>
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

    <script src="/build/js/plugins/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const siswaSelect = document.getElementById('existing_siswa_id');
            if (siswaSelect) {
                const instance = new Choices(siswaSelect, {
                    searchEnabled: true,
                    placeholder: true,
                    placeholderValue: 'Ketik nama/NIS/NISN...',
                    searchPlaceholderValue: 'Cari...',
                    shouldSort: false,
                    itemSelectText: '',
                    searchResultLimit: 15,
                    renderChoiceLimit: 15
                });

                function debounce(fn, ms) {
                    let t;
                    return (...args) => {
                        clearTimeout(t);
                        t = setTimeout(() => fn(...args), ms);
                    };
                }

                const doSearch = debounce(async (value) => {
                    const q = (value || '').trim();
                    if (q.length < 2) return;
                    const url = new URL(
                        "{{ route('akademik.kelas.ajax.search-siswa', $kelas_ajar->kelas_ajar_id) }}",
                        window.location.origin);
                    url.searchParams.set('q', q);
                    const res = await fetch(url.toString());
                    if (!res.ok) return;
                    const data = await res.json();
                    instance.setChoices((data.results || []).map(r => ({
                        value: r.id,
                        label: r.text
                    })), 'value', 'label', true);
                }, 300);

                siswaSelect.addEventListener('search', function(event) {
                    doSearch(event.detail.value);
                });
            }

            const tujuanSelect = document.getElementById('tujuan_kelas_ajar_id');
            const formPindahkan = document.getElementById('formPindahkanSiswa');
            if (tujuanSelect && formPindahkan) {
                const tujuanChoices = new Choices(tujuanSelect, {
                    searchEnabled: true,
                    placeholder: true,
                    placeholderValue: 'Cari kelas...',
                    shouldSort: false,
                    itemSelectText: '',
                    searchResultLimit: 15,
                    renderChoiceLimit: 15
                });

                let searchTimeout;
                tujuanSelect.addEventListener('search', function(event) {
                    clearTimeout(searchTimeout);
                    const q = event.detail.value;
                    if (!q || q.length < 2) return;
                    searchTimeout = setTimeout(function() {
                        const url = new URL("{{ route('akademik.ajax.kelas.search') }}", window.location.origin);
                        url.searchParams.set('kelas_ajar_id', '{{ $kelas_ajar->kelas_ajar_id }}');
                        url.searchParams.set('show_other_semester', document.getElementById('pindahShowOtherSemester').checked);
                        url.searchParams.set('show_other_tahun', document.getElementById('pindahShowOtherTahun').checked);
                        url.searchParams.set('q', q);
                        fetch(url.toString())
                            .then(res => res.json())
                            .then(data => tujuanChoices.setChoices(data.results, 'id', 'text', true));
                    }, 300);
                });

                document.querySelectorAll('.pindah-filter').forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        tujuanChoices.clearChoices();
                        tujuanChoices.clearInput();
                    });
                });

                document.querySelectorAll('.btn-pindahkan-siswa').forEach(function(button) {
                    button.addEventListener('click', function() {
                        tujuanChoices.clearStore();
                        tujuanChoices.clearChoices();
                        tujuanChoices.clearInput();
                        document.getElementById('namaSiswaPindah').textContent = button.dataset.siswaNama;
                        formPindahkan.action = "{{ url('akademik/kelas/' . $kelas_ajar->kelas_ajar_id . '/siswa') }}/" +
                            button.dataset.siswaId + '/pindahkan';
                    });
                });
            }
        });
    </script>
@endsection
