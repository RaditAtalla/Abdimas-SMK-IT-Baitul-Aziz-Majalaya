@extends('layouts.master')

@section('title', 'Penilaian Ekstrakurikuler')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')

    <x-breadcrumb item="Ekstrakurikuler" link="{{ route('ekstrakurikuler.index') }}" active="Penilaian Ekstrakurikuler" />

    <!-- [ Main Content ] start -->
    <div class="row">
        <!-- [ basic-table ] start -->
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5>{{ $ekskul->nama_pelajaran }}</h5>
                        <span class="d-block m-t-5">{{ $ekskul->tahunAjaran->tahun }} {{ $ekskul->tahunAjaran->semester }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('ekstrakurikuler.penilaian.template-excel', $ekskul->ekstrakurikuler_id) }}" class="btn btn-light-success btn-sm">
                            <i class="ti ti-file-download me-1"></i> Download Template Excel
                        </a>
                        <button type="button" class="btn btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                            <i class="ti ti-file-upload me-1"></i> Import Excel Penilaian
                        </button>
                    </div>
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
                                    <th>Siswa</th>
                                    {{-- <th data-type="date" data-format="YYYY/DD/MM">Start Date</th> --}}
                                    <th>Deskripsi Penilaian</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($peserta as $row)
                                    <tr>
                                        <td>{{ $row['nama'] }}</td>
                                        <td>{{ blank($row['deskripsi'] ?? null) ? '-' : $row['deskripsi'] }}</td>
                                        <td>
                                            @if (trim($row['deskripsi']) === '')
                                                <button type="button" class="btn btn-sm btn-light-primary"
                                                    data-bs-toggle="modal" data-bs-target="#penilaianModal"
                                                    data-id="{{ $row['siswa_ekstrakurikuler_id'] }}"
                                                    data-nama="{{ $row['nama'] }}" data-deskripsi="">
                                                    Tambah
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-light-warning"
                                                    data-bs-toggle="modal" data-bs-target="#penilaianModal"
                                                    data-id="{{ $row['siswa_ekstrakurikuler_id'] }}"
                                                    data-nama="{{ $row['nama'] }}"
                                                    data-deskripsi="{{ $row['deskripsi'] }}">
                                                    Edit
                                                </button>
                                                <form
                                                    action="{{ route('penilaian_ekstrakurikuler.destroy', [$ekskul->ekstrakurikuler_id, $row['siswa_ekstrakurikuler_id']]) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus penilaian ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-light-danger">Hapus</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada peserta pada ekstrakurikuler ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <a href="{{ route('ekstrakurikuler.index') }}" class="btn btn-light-secondary">Kembali</a>
                </div>
            </div>
        </div>
        <!-- [ basic-table ] end -->
    </div>
    <!-- [ Main Content ] end -->

    <!-- Modal Edit Penilaian -->
    <div class="modal fade" id="penilaianModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="penilaianForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah/Edit Penilaian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="siswa_ekstrakurikuler_id" id="siswaEkstrakurikulerId">
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Penilaian</label>
                            <input type="text" class="form-control" id="deskripsiPenilaianInput" name="deskripsi"
                                placeholder="Masukkan deskripsi penilaian" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSimpan">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel Penilaian -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('ekstrakurikuler.penilaian.import-excel', $ekskul->ekstrakurikuler_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Excel Penilaian Ekstrakurikuler</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih File Excel (.xlsx / .xls)</label>
                            <input type="file" class="form-control" name="file_excel" accept=".xlsx, .xls" required>
                            <div class="form-text text-muted">
                                Gunakan template Excel yang telah diunduh dari tombol "Download Template Excel".
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Import</button>
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
        const modal = document.getElementById('penilaianModal');
        const form = document.getElementById('penilaianForm');
        const siswaIdInput = document.getElementById('siswaEkstrakurikulerId');
        const deskripsiInput = document.getElementById('deskripsiPenilaianInput');
        const formMethod = document.getElementById('formMethod');
        const modalTitle = document.getElementById('modalTitle');

        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const siswaId = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const deskripsi = button.getAttribute('data-deskripsi');

            siswaIdInput.value = siswaId;
            deskripsiInput.value = deskripsi;

            if (deskripsi === '') {
                // Tambah
                form.action = "{{ route('penilaian_ekstrakurikuler.store', $ekskul->ekstrakurikuler_id) }}";
                formMethod.value = "POST";
                modalTitle.textContent = "Tambah Penilaian untuk " + nama;
            } else {
                // Edit
                form.action =
                    "{{ url('ekstrakurikuler/' . $ekskul->ekstrakurikuler_id . '/penilaian_ekstrakurikuler') }}/" +
                    siswaId;
                formMethod.value = "PUT";
                modalTitle.textContent = "Edit Penilaian untuk " + nama;
            }
        });
    </script>
@endsection
