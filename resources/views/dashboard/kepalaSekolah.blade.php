@extends('layouts.master')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
    <x-breadcrumb item="Beranda" active="Kepala Sekolah" />

    {{-- @php
        dd([
            'rerataSiswa' => $rerataSiswa,
            'dataGanjil' => $dataGanjil,
            'dataGenap' => $dataGenap,
            'categories' => $categories,
        ]);
    @endphp --}}

    {{-- Rerata Nilai Tiap Kelas Paralel (Cashflow style) --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="mb-1">Rerata Nilai Kelas Paralel</h5>
                </div>
                {{-- Jika ingin filter, bisa tambahkan select di sini --}}
            </div>
            <div id="cashflow-bar-chart"></div>
        </div>
    </div>

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
        {{-- Kehadiran Siswa --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Kehadiran Siswa</div>
                <div class="card-body">
                    <div id="chart-kehadiran-siswa"></div>
                    <div class="text-center mt-2">
                        <small>Persentase kehadiran siswa semester ini</small>
                    </div>
                </div>
            </div>
        </div>
        {{-- Kehadiran Guru --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Kehadiran Guru</div>
                <div class="card-body">
                    <div id="chart-kehadiran-guru"></div>
                    <div class="text-center mt-2">
                        <small>Belum tersedia</small>
                    </div>
                </div>
            </div>
        </div>
        {{-- Rerata Nilai Semua Mapel --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Rerata Nilai Semua Mapel</div>
                <div class="card-body">
                    <div id="chart-rerata-nilai"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        {{-- Rerata Nilai Kelas Paralel --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Rerata Nilai Tiap Kelas Paralel</div>
                <div class="card-body">
                    <div id="chart-rerata-kelas"></div>
                </div>
            </div>
        </div>
        {{-- Siswa Berprestasi --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Siswa Berprestasi</div>
                <div class="card-body">
                    <div id="chart-rerata-siswa"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/build/js/plugins/apexcharts.min.js"></script>

    <script>
        // Data dari controller
        const categories = @json($categories);
        const dataGenap = @json($dataGenap);
        const dataGanjil = @json($dataGanjil);

        // Cashflow Bar Chart (Rerata Nilai Kelas Paralel, bar berdampingan)
        var cashflow_bar_chart_options = {
            chart: {
                type: 'bar',
                height: 210,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    columnWidth: '70%',
                    borderRadius: 2
                }
            },
            fill: {
                opacity: [1, 0.4]
            },
            stroke: {
                show: true,
                width: 3,
                colors: ['transparent']
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                show: true,
                fontFamily: `'Public Sans', sans-serif`,
                offsetX: 10,
                offsetY: 10,
                labels: {
                    useSeriesColors: false
                },
                markers: {
                    width: 10,
                    height: 10,
                    radius: '50%',
                    offsexX: 2,
                    offsexY: 2
                },
                itemMargin: {
                    horizontal: 15,
                    vertical: 5
                }
            },
            colors: ['#1e3a8a', '#60a5fa'], // Biru tua (genap), biru muda (ganjil)
            series: [{
                    name: 'Semester Genap',
                    data: dataGenap
                },
                {
                    name: 'Semester Ganjil',
                    data: dataGanjil
                }
            ],
            grid: {
                borderColor: '#00000010'
            },
            xaxis: {
                categories: categories
            },
            yaxis: {
                show: false
            }
        };
        new ApexCharts(document.querySelector('#cashflow-bar-chart'), cashflow_bar_chart_options).render();
    </script>

    <script>
        // Data dari controller
        const persenKehadiranSiswa = @json($persenKehadiranSiswa);
        const persenKehadiranGuru = @json($persenKehadiranGuru);
        const rerataNilai = @json($rerataNilai);
        const rerataKelas = @json($rerataKelas);
        const rerataSiswa = @json(array_slice($rerataSiswa, 0, 10)); // Top 10

        // Kehadiran Siswa - RadialBar
        new ApexCharts(document.querySelector("#chart-kehadiran-siswa"), {
            chart: {
                type: 'radialBar',
                height: 220
            },
            series: [persenKehadiranSiswa],
            labels: ['Siswa'],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '60%'
                    },
                    dataLabels: {
                        name: {
                            show: true
                        },
                        value: {
                            show: true,
                            fontSize: '32px',
                            formatter: v => v + '%'
                        }
                    }
                }
            },
            colors: ['#3b82f6']
        }).render();

        // Kehadiran Guru - RadialBar (greyed out)
        new ApexCharts(document.querySelector("#chart-kehadiran-guru"), {
            chart: {
                type: 'radialBar',
                height: 220
            },
            series: [0],
            labels: ['Guru'],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '60%'
                    },
                    dataLabels: {
                        name: {
                            show: true
                        },
                        value: {
                            show: true,
                            fontSize: '32px',
                            formatter: () => '-'
                        }
                    }
                }
            },
            colors: ['#d1d5db']
        }).render();

        // Rerata Nilai Semua Mapel
        if (rerataNilai.length > 0) {
            new ApexCharts(document.querySelector("#chart-rerata-nilai"), {
                chart: {
                    type: 'bar',
                    height: 250
                },
                series: [{
                    name: 'Rerata',
                    data: rerataNilai.map(x => x.rerata)
                }],
                xaxis: {
                    categories: rerataNilai.map(x => x.nama_mapel)
                }
            }).render();
        }

        // Rerata Nilai Kelas Paralel
        if (rerataKelas.length > 0) {
            new ApexCharts(document.querySelector("#chart-rerata-kelas"), {
                chart: {
                    type: 'bar',
                    height: 250
                },
                series: [{
                    name: 'Rerata',
                    data: rerataKelas.map(x => x.rerata)
                }],
                xaxis: {
                    categories: rerataKelas.map(x => x.nama_kelas)
                }
            }).render();
        }

        // Siswa Berprestasi
        if (rerataSiswa.length > 0) {
            new ApexCharts(document.querySelector("#chart-rerata-siswa"), {
                chart: {
                    type: 'bar',
                    height: 250
                },
                series: [{
                    name: 'Rerata',
                    data: rerataSiswa.map(x => x.rerata)
                }],
                xaxis: {
                    categories: rerataSiswa.map(x => `${x.nama_siswa} ${x.kelas}`)
                }
            }).render();
        }
    </script>
@endsection
