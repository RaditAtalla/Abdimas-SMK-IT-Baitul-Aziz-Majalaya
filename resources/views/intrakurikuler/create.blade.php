@extends('layouts.master')

@section('title', 'Tambah Intrakurikuler')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')
    <x-breadcrumb item="Intrakurikuler" subItem="Intrakurikuler" subLink="{{ route('intrakurikuler.index') }}" active="Tambah Intrakurikuler" />

    <div class="container">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Tambah Intrakurikuler</h5>
                    <span class="d-block m-t-5">Tambahkan mapel dan tentukan guru pengampu</span>
                </div>

                <div class="card-body">
                    <form action="{{ route('intrakurikuler.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label>Mata Pelajaran</label>
                            <input type="text" name="nama_pelajaran" value="{{ old('nama_pelajaran') }}"
                                class="form-control @error('nama_pelajaran') is-invalid @enderror">
                            @error('nama_pelajaran')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Kelas Ajar</label>
                            <select name="kelas_ajar_id" class="form-control @error('kelas_ajar_id') is-invalid @enderror">
                                <option value="">Pilih Kelas Ajar</option>
                                @foreach ($kelasAjar as $ka)
                                    <option value="{{ $ka->kelas_ajar_id }}"
                                        {{ old('kelas_ajar_id') == $ka->kelas_ajar_id ? 'selected' : '' }}>
                                        {{ $ka->tahunAjaran->tahun }} {{ $ka->tahunAjaran->semester }} •
                                        {{ $ka->kelas->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_ajar_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Guru Pengampu</label>
                            <select name="pengampu_user_id"
                                class="form-control @error('pengampu_user_id') is-invalid @enderror">
                                <option value="">Pilih Guru</option>
                                @foreach ($guru as $g)
                                    <option value="{{ $g->id }}"
                                        {{ old('pengampu_user_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->staff?->nama ?? $g->name }}
                                        @if ($g->staff?->nuptk)
                                            - NUPTK: {{ $g->staff->nuptk }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('pengampu_user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button class="btn btn-success">Simpan</button>
                        <a href="{{ route('intrakurikuler.index') }}" class="btn btn-light">Batal</a>
                    </form>
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
