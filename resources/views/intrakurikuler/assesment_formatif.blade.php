@extends('layouts.master')

@section('title', 'Asesmen Formatif')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')

    <x-breadcrumb item="Intrakurikuler" subItem="Intrakurikuler" subLink="{{ route('intrakurikuler.index') }}" active="Asesmen Formatif" />

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

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('templateFormatif', $intrakurikuler->intrakurikuler_id) }}"
                            class="btn btn-primary">
                            <i class="bi bi-download"></i> Unduh Template Excel
                        </a>
                        <form action="{{ route('assesment-formatif.import', $intrakurikuler->intrakurikuler_id) }}"
                            method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                            @csrf
                            <label class="btn btn-outline-secondary mb-0">
                                <i class="bi bi-upload"></i> Pilih File Excel
                                <input type="file" name="file" accept=".xlsx,.xls" class="d-none" required
                                    onchange="this.form.querySelector('button[type=submit]').disabled = !this.value;">
                            </label>
                            <button type="submit" class="btn btn-success" disabled>
                                <i class="bi bi-save"></i> Simpan Data Excel
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Body --}}
                <div class="card-body table-border-style">
                    @if (session('success'))
                        <div class="alert alert-success mb-3">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>TP Tercapai</th>
                                    <th>TP Tidak Tercapai</th>
                                    <th>Deskripsi Capaian Tertinggi</th>
                                    <th>Deskripsi Capaian Terendah</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $r)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $r['nama'] }}</td>
                                        <td>
                                            <span class="badge f-12 bg-success">{{ $r['tp_tercapai'] }} dari
                                                {{ $r['tp_total'] }}</span>
                                        </td>
                                        <td>
                                            <span class="badge f-12 bg-danger">{{ $r['tp_tidak_tercapai'] }} dari
                                                {{ $r['tp_total'] }}</span>
                                        </td>
                                        <td class="text-wrap">
                                            <span title="{{ $r['capaian_tertinggi'] }}">
                                                {{ \Illuminate\Support\Str::limit($r['capaian_tertinggi'], 150) }}
                                            </span>
                                        </td>
                                        <td class="text-wrap">
                                            <span title="{{ $r['capaian_terendah'] }}">
                                                {{ \Illuminate\Support\Str::limit($r['capaian_terendah'], 150) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a
                                                href="{{ route('assesment-formatif.detail', [
                                                    'intrakurikuler' => $intrakurikuler->intrakurikuler_id,
                                                    'riwayatKelas' => $r['riwayat_kelas_id'],
                                                ]) }}">
                                                <button type="button" class="btn btn-sm btn-light-primary">Detail
                                                    Nilai</button>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada siswa di kelas ini.</td>
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

@endsection

@section('scripts')
    <!-- [Page Specific JS] start -->
    <script type="module">
        import {
            DataTable
        } from '/build/js/plugins/module.js';
        window.dt = new DataTable('#pc-dt-simple');
    </script>

    <script>
        const modalEl = document.getElementById('tujuanPembelajaranModal');

        modalEl.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;

            const mode = btn.getAttribute('data-mode') || 'create';
            const title = btn.getAttribute('data-title') || 'Tambah Lingkup Materi';

            const id = btn.getAttribute('data-id') || '';
            const nama = btn.getAttribute('data-nama') || '';

            // title
            document.getElementById('tujuanPembelajaranModalTitle').textContent = title;

            // fill input
            document.getElementById('lm_id').value = id;
            document.getElementById('lm_nama').value = nama;

            // set form action + method
            const form = document.getElementById('lingkupMateriForm');
            const methodSpoof = document.getElementById('methodSpoof');
            methodSpoof.innerHTML = '';

            if (mode === 'edit' && id) {
                form.action = `/lingkup-materi/${id}`; // <- SESUAIKAN kalau route kamu beda
                methodSpoof.innerHTML = `@method('PUT')`;
            } else {
                form.action = `/lingkup-materi`; // <- SESUAIKAN kalau route kamu beda
            }
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            // optional reset ketika modal ditutup
            document.getElementById('lm_id').value = '';
            document.getElementById('lm_nama').value = '';
            document.getElementById('methodSpoof').innerHTML = '';
            document.getElementById('lingkupMateriForm').action = `/lingkup-materi`;
            document.getElementById('tujuanPembelajaranModalTitle').textContent = 'Tambah Lingkup Materi';
        });
    </script>
    <!-- [Page Specific JS] end -->
@endsection
