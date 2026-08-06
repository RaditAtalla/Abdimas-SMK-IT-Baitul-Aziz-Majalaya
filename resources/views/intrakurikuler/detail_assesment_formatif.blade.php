@extends('layouts.master')

@section('title', 'Asesmen Formatif')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')

    <x-breadcrumb item="Intrakurikuler" subItem="Intrakurikuler" subLink="{{ route('intrakurikuler.index') }}" sub2Item="Asesmen Formatif" sub2Link="{{ route('assesment-formatif.index', $intrakurikuler->intrakurikuler_id) }}" active="Detail Asesmen Formatif" />

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-4">{{ $riwayatKelas->siswa->user->name ?? $riwayatKelas->siswa->nama }}</h5>
                    <span class="d-block m-t-5">
                        {{ $riwayatKelas->kelasAjar->kelas->nama_kelas }} •
                        {{ $riwayatKelas->kelasAjar->tahunAjaran->tahun }}
                        {{ $riwayatKelas->kelasAjar->tahunAjaran->semester }}
                    </span>
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
                    <form method="POST"
                        action="{{ route('assesment-formatif.save-detail', [$intrakurikuler->intrakurikuler_id, $riwayatKelas->riwayat_kelas_id]) }}">
                        @csrf
                        <div class="row">
                            <div class="mb-4 col-md-6">
                                <label class="form-label fs-5 f-w-600">Deskripsi Capaian Tertinggi dalam Rapor</label>
                                <textarea class="form-control" id="capaianTertinggi" name="capaian_tertinggi" rows="4">{{ old('capaian_tertinggi') }}</textarea>
                                {{-- <textarea class="form-control" id="capaianTertinggi" name="capaian_tertinggi" rows="4">{{ old('capaian_tertinggi', ($riwayatKelas->siswa->user->name ?? $riwayatKelas->siswa->nama) . ' menunjukkan pemahaman dalam ') }}</textarea> --}}
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label fs-5 f-w-600">Deskripsi Capaian Terendah dalam Rapor</label>
                                <textarea class="form-control" id="capaianTerendah" name="capaian_terendah" rows="4">{{ old('capaian_terendah') }}</textarea>
                                {{-- <textarea class="form-control" id="capaianTerendah" name="capaian_terendah" rows="4">{{ old('capaian_terendah', ($riwayatKelas->siswa->user->name ?? $riwayatKelas->siswa->nama) . ' membutuhkan bimbingan dalam ') }}</textarea> --}}
                            </div>

                            @foreach ($intrakurikuler->tujuanPembelajaran as $tp)
                                @php
                                    $detail = $details
                                        ->where('tujuan_pembelajaran_id', $tp->tujuan_pembelajaran_id)
                                        ->first();
                                    $tercapai = $detail && $detail->kktp ? true : false;
                                    // $tampil_rapor = $detail && isset($detail->tampil) ? (bool) $detail->tampil : false;
                                    $tampil_rapor = $detail ? (bool) $detail->tampil : false;
                                    $tpIndex = $loop->iteration;
                                @endphp
                                <div class="mb-4 col-md-6">
                                    <label class="form-label fs-5 f-w-600">TP {{ $tpIndex }}</label>
                                    <p class="tp-deskripsi" data-tp="{{ $tp->tujuan_pembelajaran_id }}">
                                        {{ $tp->deskripsi }}
                                    </p>
                                    <div class="form-check">
                                        <input class="form-check-input tp-tercapai" type="checkbox"
                                            name="tp[{{ $tp->tujuan_pembelajaran_id }}][tercapai]" value="1"
                                            id="tp{{ $tpIndex }}_tercapai" {{ $tercapai ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tp{{ $tpIndex }}_tercapai">
                                            TP tercapai
                                        </label>
                                    </div>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input tp-tampil-rapor" type="checkbox"
                                            name="tp[{{ $tp->tujuan_pembelajaran_id }}][tampil_rapor]" value="1"
                                            id="tp{{ $tpIndex }}_rapor" {{ $tampil_rapor ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tp{{ $tpIndex }}_rapor">
                                            Tampilkan di rapor
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="{{ route('assesment-formatif.index', $intrakurikuler->intrakurikuler_id) }}"
                                    class="btn btn-light-secondary">Kembali</a>
                            </div>
                            <div class="d-flex gap-5">
                                @if ($prevSiswa)
                                    <a href="{{ route('assesment-formatif.detail', [
                                        'intrakurikuler' => $intrakurikuler->intrakurikuler_id,
                                        'riwayatKelas' => $prevSiswa->riwayat_kelas_id,
                                    ]) }}"
                                        class="link-primary">
                                        <i class="bi bi-chevron-left"></i>
                                        {{ $prevSiswa->siswa->user->name ?? $prevSiswa->siswa->nama }}
                                    </a>
                                @else
                                    <span class="text-muted"><i class="bi bi-chevron-left"></i> (Siswa pertama)</span>
                                @endif

                                @if ($nextSiswa)
                                    <a href="{{ route('assesment-formatif.detail', [
                                        'intrakurikuler' => $intrakurikuler->intrakurikuler_id,
                                        'riwayatKelas' => $nextSiswa->riwayat_kelas_id,
                                    ]) }}"
                                        class="link-primary">
                                        {{ $nextSiswa->siswa->user->name ?? $nextSiswa->siswa->nama }} <i
                                            class="bi bi-chevron-right"></i>
                                    </a>
                                @else
                                    <span class="text-muted">(Siswa terakhir) <i class="bi bi-chevron-right"></i></span>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
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
        function updateCapaianTextareas() {
            let namaSiswa = @json($riwayatKelas->siswa->user->name ?? $riwayatKelas->siswa->nama);
            let tpTercapai = [];
            let tpTidakTercapai = [];

            // Loop semua TP yang ada di form
            document.querySelectorAll('.tp-deskripsi').forEach(function(descEl) {
                let tpId = descEl.getAttribute('data-tp');
                let deskripsi = descEl.textContent.trim();

                let tercapai = document.querySelector('input[name="tp[' + tpId + '][tercapai]"]')?.checked;
                let tampilRapor = document.querySelector('input[name="tp[' + tpId + '][tampil_rapor]"]')?.checked;

                if (tampilRapor) {
                    if (tercapai) {
                        tpTercapai.push(deskripsi);
                    } else {
                        tpTidakTercapai.push(deskripsi);
                    }
                }
            });

            let tertinggi = `${namaSiswa} menunjukkan pemahaman dalam ` + (tpTercapai.length > 0 ? tpTercapai.join(', ') :
                '');
            let terendah = `${namaSiswa} membutuhkan bimbingan dalam ` + (tpTidakTercapai.length > 0 ? tpTidakTercapai.join(
                ', ') : '');

            document.getElementById('capaianTertinggi').value = tertinggi;
            document.getElementById('capaianTerendah').value = terendah;
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCapaianTextareas();
            document.querySelectorAll('.tp-tercapai, .tp-tampil-rapor').forEach(function(el) {
                el.addEventListener('change', updateCapaianTextareas);
            });
            // Pastikan update sebelum submit juga
            document.querySelector('form').addEventListener('submit', function() {
                updateCapaianTextareas();
            });
        });
    </script>
@endsection
