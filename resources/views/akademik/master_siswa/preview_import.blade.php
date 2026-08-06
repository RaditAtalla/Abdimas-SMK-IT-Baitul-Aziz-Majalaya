@extends('layouts.master')

@section('title', 'Pratinjau Impor Data Siswa')

@section('css')
    <style>
        .preview-table-container {
            max-height: 650px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
        }

        .preview-table th {
            position: sticky;
            top: 0;
            background-color: #2b579a !important;
            color: #ffffff !important;
            z-index: 2;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 10px 8px;
        }

        .preview-table td {
            vertical-align: middle;
            padding: 6px 8px;
            white-space: nowrap;
        }

        .preview-table .form-control-sm,
        .preview-table .form-select-sm {
            font-size: 0.82rem;
            padding: 4px 8px;
            border-radius: 4px;
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb item="Manajemen Siswa" active="Pratinjau Impor Excel" />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Pratinjau & Edit Data Impor Siswa</h5>
                        <small class="text-muted">Tabel memanjang berbentuk spreadsheet interaktif. Anda dapat menyunting nilai di kotak input atau menghapus baris sebelum disimpan.</small>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light-success text-success fs-6 p-2">
                            <i class="bi bi-person-plus me-1"></i> <span id="countNewText">{{ $countNew }}</span> Siswa Baru
                        </span>
                        @if ($countUpdate > 0)
                            <span class="badge bg-light-warning text-warning fs-6 p-2">
                                <i class="bi bi-pencil-square me-1"></i> <span id="countUpdateText">{{ $countUpdate }}</span> Update Data
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if ($countUpdate > 0)
                        <div class="alert alert-warning mb-3 py-2 small">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Terdapat <strong>{{ $countUpdate }} data siswa</strong> yang NIS/NISN-nya sudah terdaftar di sistem. Mengonfirmasi akan <strong>memperbarui profil siswa & orang tua tersebut</strong>.
                        </div>
                    @else
                        <div class="alert alert-info mb-3 py-2 small">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Semua data di bawah ini siap ditambahkan sebagai <strong>Siswa Baru</strong>. Silakan periksa atau sunting langsung pada kotak input tabel jika diperlukan.
                        </div>
                    @endif

                    <form action="{{ route('akademik.master-siswa.confirm-import') }}" method="POST" id="formConfirmImport">
                        @csrf

                        <div class="preview-table-container mb-3">
                            <table class="table table-bordered table-striped table-hover preview-table" id="previewTable" style="min-width: 2400px;">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th style="width: 110px;">Status</th>
                                        <th style="width: 140px;">NIS*</th>
                                        <th style="width: 140px;">NISN*</th>
                                        <th style="width: 220px;">Nama Siswa*</th>
                                        <th style="width: 110px;">JK (L/P)*</th>
                                        <th style="width: 170px;">Tempat Lahir</th>
                                        <th style="width: 150px;">Tgl Lahir*</th>
                                        <th style="width: 120px;">Agama*</th>
                                        <th style="width: 180px;">Sekolah Asal*</th>
                                        <th style="width: 180px;">Nama Ayah*</th>
                                        <th style="width: 180px;">Nama Ibu*</th>
                                        <th style="width: 150px;">Pekerjaan Ayah</th>
                                        <th style="width: 150px;">Pekerjaan Ibu</th>
                                        <th style="width: 260px;">Alamat (Jalan / RT / RW)*</th>
                                        <th style="width: 170px;">Kelurahan / Desa</th>
                                        <th style="width: 60px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($previewRows as $index => $row)
                                        <tr id="row-{{ $index }}">
                                            <td class="text-center fw-bold row-number">{{ $loop->iteration }}</td>
                                            <td class="text-center">
                                                @if ($row['is_update'])
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-pencil me-1"></i>Perbarui</span>
                                                @else
                                                    <span class="badge bg-success"><i class="bi bi-plus-circle me-1"></i>Baru</span>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][nis]" class="form-control form-control-sm fw-bold" value="{{ $row['nis'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][nisn]" class="form-control form-control-sm" value="{{ $row['nisn'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][nama]" class="form-control form-control-sm fw-bold text-primary" value="{{ $row['nama'] }}" required>
                                            </td>
                                            <td>
                                                <select name="rows[{{ $index }}][jk]" class="form-select form-select-sm" required>
                                                    <option value="l" {{ strtolower($row['jk']) == 'l' ? 'selected' : '' }}>L (Laki-laki)</option>
                                                    <option value="p" {{ strtolower($row['jk']) == 'p' ? 'selected' : '' }}>P (Perempuan)</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][kab_text]" class="form-control form-control-sm" value="{{ $row['kab_text'] ?: $row['kab_label'] }}" placeholder="misal: Bandung">
                                            </td>
                                            <td>
                                                <input type="date" name="rows[{{ $index }}][tgl_lahir]" class="form-control form-control-sm" value="{{ $row['tgl_lahir'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][agama]" class="form-control form-control-sm" value="{{ $row['agama'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][pendidikan_prev]" class="form-control form-control-sm" value="{{ $row['pendidikan_prev'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][nama_ayah]" class="form-control form-control-sm" value="{{ $row['nama_ayah'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][nama_ibu]" class="form-control form-control-sm" value="{{ $row['nama_ibu'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][pek_ayah]" class="form-control form-control-sm" value="{{ $row['pek_ayah'] }}">
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][pek_ibu]" class="form-control form-control-sm" value="{{ $row['pek_ibu'] }}">
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][alamat]" class="form-control form-control-sm" value="{{ $row['alamat'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][kel_text]" class="form-control form-control-sm" value="{{ $row['kel_text'] ?: $row['kel_label'] }}" placeholder="misal: Majalaya">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-light-danger remove-row-btn" title="Hapus Baris Ini">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded border flex-wrap gap-2">
                            <div>
                                <span class="fw-bold fs-6">
                                    Total <span id="totalRowsCounter" class="text-primary">{{ count($previewRows) }}</span> Siswa Siap Diimpor
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('akademik.master-siswa.index') }}" class="btn btn-light">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="bi bi-check-circle me-1"></i> Konfirmasi & Simpan ke Database
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableBody = document.querySelector('#previewTable tbody');
            const counterEl = document.getElementById('totalRowsCounter');
            const submitBtn = document.getElementById('submitBtn');

            tableBody.addEventListener('click', function(e) {
                const btn = e.target.closest('.remove-row-btn');
                if (btn) {
                    const tr = btn.closest('tr');
                    if (tr) {
                        tr.remove();
                        reindexRows();
                    }
                }
            });

            function reindexRows() {
                const rows = tableBody.querySelectorAll('tr');
                counterEl.innerText = rows.length;

                rows.forEach((tr, index) => {
                    const numTd = tr.querySelector('.row-number');
                    if (numTd) {
                        numTd.innerText = index + 1;
                    }
                });

                if (rows.length === 0) {
                    if (submitBtn) submitBtn.disabled = true;
                }
            }
        });
    </script>
@endsection
