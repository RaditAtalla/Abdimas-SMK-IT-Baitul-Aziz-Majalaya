@extends('layouts.master')

@section('title', 'Cetak Dokumen')

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
    <x-breadcrumb item="Dokumen" active="Cetak Dokumen" />
    <div class="card">
        <div class="card-header">
            <h5>Daftar Kelas</h5>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <table class="table" id="pc-dt-simple">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Tahun Ajaran</th>
                        <th>Semester</th>
                        <th>Wali Kelas</th>
                        <th>Jumlah Siswa</th>
                        @role('Bagian Akademik')
                            <th>Aksi</th>
                        @endrole
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kelasList as $kelas)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $kelas->kelas->nama_kelas }}</td>
                            <td>{{ $kelas->tahunAjaran->tahun }}</td>
                            <td>{{ $kelas->tahunAjaran->semester }}</td>
                            @role('Bagian Akademik')
                                <td>{{ $kelas->waliKelas->name ?? '-' }}</td>
                            @endrole
                            <td>{{ $kelas->riwayat_kelas_count ?? 0 }}</td>
                            <td>
                                <a href="{{ route('dokumen.kelas.pilih', $kelas->kelas_ajar_id) }}"
                                    class="btn btn-primary btn-sm">Cetak</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
