@extends('layouts.master')

@section('title', 'Asesmen Sumatif')

@section('css')
<link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')

<x-breadcrumb item="Intrakurikuler" subItem="Intrakurikuler" subLink="{{ route('intrakurikuler.index') }}" active="Asesmen Sumatif" />

<div class="row">
    <div class="col-xl-12">
        <div class="card">

            {{-- Header --}}
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">{{ $intrakurikuler->nama_pelajaran }}</h5>
                    <span class="d-block m-t-5">{{ $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '-' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('assesment_sumatif.template', $intrakurikuler->intrakurikuler_id) }}" class="btn btn-primary">
                        <i class="bi bi-download"></i> Unduh Template Excel
                    </a>

                    <form action="{{ route('assesment_sumatif.import', $intrakurikuler->intrakurikuler_id) }}"
                        method="POST" enctype="multipart/form-data"
                        class="d-flex align-items-center gap-2">
                        @csrf
                        <label class="btn btn-outline-secondary mb-0">
                            <i class="bi bi-upload"></i> Pilih File Excel
                            <input type="file" name="excel" accept=".xlsx,.xls" class="d-none" required
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
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th style="width:60px;">No</th>
                                <th>Nama</th>
                                <th style="width:180px;">Total Lingkup Materi</th>
                                <th style="width:180px;">Total Akhir Semester</th>
                                <th style="width:140px;">Nilai Rapor</th>
                                <th style="width:160px;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($rows as $r)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $r['nama'] }}</td>
                                <td><span class="badge bg-light-primary">{{ $r['total_lingkup_materi'] ?? '-' }}</span></td>
                                <td><span class="badge bg-light-primary">{{ $r['total_akhir_semester'] ?? '-' }}</span></td>
                                <td><span class="badge bg-light-primary">{{ $r['nilai_rapor'] ?? '-' }}</span></td>
                                <td>
                                    {{--
                                        - sumatif akhir lingkup materi (ambil datanya)
                                        - sumatif akhir Semester
                                        - 
                                    --}}
                                    <a href="{{ route('assesment_sumatif.detail', [
                                        'intrakurikuler' => $intrakurikuler->intrakurikuler_id,
                                        'riwayatKelas' => $r['riwayat_kelas_id'],
                                    ]) }}">
                                        <button type="button" class="btn btn-sm btn-light-primary">Detail Nilai</button>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada siswa di kelas ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('intrakurikuler.index') }}" class="btn btn-light-secondary">Kembali</a>
            </div>
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
@endsection