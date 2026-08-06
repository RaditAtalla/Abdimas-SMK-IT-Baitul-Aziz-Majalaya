@extends('layouts.master')

@section('title', 'Dashboard Siswa')

@section('content')
    <x-breadcrumb item="Beranda" active="Siswa" />

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <label>Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-control" onchange="this.form.submit()">
                        @foreach ($tahunAjaranList as $ta)
                            <option value="{{ $ta->tahun_ajaran_id }}"
                                {{ $tahunAjaranAktif && $ta->tahun_ajaran_id == $tahunAjaranAktif->tahun_ajaran_id ? 'selected' : '' }}>
                                {{ $ta->tahun }} (Semester {{ $ta->semester }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- Distribusi Nilai Per Semester --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Distribusi Nilai Per Mapel</div>
                <div class="card-body">
                    <div id="chart-nilai-distribusi"></div>
                </div>
            </div>
        </div>
        {{-- Perkembangan Nilai Keseluruhan --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Perkembangan Nilai Keseluruhan</div>
                <div class="card-body">
                    <div id="chart-perkembangan-nilai"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/build/js/plugins/apexcharts.min.js"></script>
    <script>
        // Data dari controller
        const nilaiDistribusi = @json($nilaiDistribusi);
        const perkembanganNilai = @json($perkembanganNilai);

        // Distribusi Nilai Per Mapel (Bar)
        if (nilaiDistribusi.length > 0) {
            new ApexCharts(document.querySelector("#chart-nilai-distribusi"), {
                chart: {
                    type: 'bar',
                    height: 250
                },
                series: [{
                    name: 'Rerata',
                    data: nilaiDistribusi.map(x => x.rerata)
                }],
                xaxis: {
                    categories: nilaiDistribusi.map(x => x.mapel)
                }
            }).render();
        }

        // Perkembangan Nilai Keseluruhan (Line)
        if (perkembanganNilai.length > 0) {
            new ApexCharts(document.querySelector("#chart-perkembangan-nilai"), {
                chart: {
                    type: 'line',
                    height: 250
                },
                series: [{
                    name: 'Rerata',
                    data: perkembanganNilai.map(x => x.rerata)
                }],
                xaxis: {
                    categories: perkembanganNilai.map(x => x.label)
                }
            }).render();
        }
    </script>
@endsection
