@extends('layouts.master')

@section('title', 'Dashboard Akademik')

@section('content')
    <x-breadcrumb item="Beranda" active="Akademik" />

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-select" onchange="this.form.submit()">
                        @foreach ($tahunAjaranList as $ta)
                            <option value="{{ $ta->tahun_ajaran_id }}"
                                {{ $tahunAjaranAktif && $ta->tahun_ajaran_id == $tahunAjaranAktif->tahun_ajaran_id ? 'selected' : '' }}>
                                {{ $ta->tahun }} ({{ $ta->semester }})
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Jika ingin filter semester terpisah, tambahkan di sini --}}
            </form>
        </div>
    </div>

    <div class="row">

        {{-- Rerata Semua Mapel --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5>Rerata Nilai Semua Mapel</h5>
                </div>
                <div class="card-body">
                    @if (count($rerataNilai) > 0)
                        <div id="chart-rerata-nilai"></div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bar-chart" style="font-size:2rem;"></i><br>
                            Data rerata nilai mapel belum tersedia untuk tahun ajaran ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Minat Ekstrakurikuler --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5>Minat Ekstrakurikuler</h5>
                </div>
                <div class="card-body">
                    @if (count($minatEkskul) > 0 && collect($minatEkskul)->sum('jumlah') > 0)
                        <div id="chart-minat-ekskul"></div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-people" style="font-size:2rem;"></i><br>
                            Data minat ekstrakurikuler belum tersedia untuk tahun ajaran ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Minat Intrakurikuler --}}
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5>Minat Intrakurikuler (Mapel)</h5>
                </div>
                <div class="card-body">
                    @if (count($minatIntrakurikuler) > 0 && collect($minatIntrakurikuler)->sum('jumlah') > 0)
                        <div id="chart-minat-intrakurikuler"></div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-people" style="font-size:2rem;"></i><br>
                            Data minat intrakurikuler belum tersedia untuk tahun ajaran ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/build/js/plugins/apexcharts.min.js"></script>
    <script src="/build/js/chart_maps/chart-apex.js"></script>

    <script>
        // Data dari controller
        const rerataNilai = @json($rerataNilai);
        const minatEkskul = @json($minatEkskul);
        const minatIntrakurikuler = @json($minatIntrakurikuler);

        console.log(minatIntrakurikuler);

        if (rerataNilai.length > 0) {
            new ApexCharts(document.querySelector("#chart-rerata-nilai"), {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Rerata Nilai',
                    data: rerataNilai.map(x => x.rerata)
                }],
                xaxis: {
                    categories: rerataNilai.map(x => x.nama_pelajaran),
                    labels: {
                        rotate: -45
                    }
                },
                yaxis: {
                    min: 0,
                    max: 100
                }
            }).render();
        }

        if (minatEkskul.length > 0 && minatEkskul.reduce((a, b) => a + b.jumlah, 0) > 0) {
            new ApexCharts(document.querySelector("#chart-minat-ekskul"), {
                chart: {
                    type: 'donut',
                    height: 350
                },
                series: minatEkskul.map(x => x.jumlah),
                labels: minatEkskul.map(x => x.nama_ekskul),
                legend: {
                    position: 'bottom'
                }
            }).render();
        }

        if (minatIntrakurikuler.length > 0 && minatIntrakurikuler.reduce((a, b) => a + b.jumlah, 0) > 0) {
            new ApexCharts(document.querySelector("#chart-minat-intrakurikuler"), {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Jumlah Siswa',
                    data: minatIntrakurikuler.map(x => x.jumlah)
                }],
                xaxis: {
                    categories: minatIntrakurikuler.map(x => x.nama_pelajaran),
                    labels: {
                        rotate: -45
                    }
                },
                yaxis: {
                    min: 0
                }
            }).render();
        }
    </script>
@endsection
