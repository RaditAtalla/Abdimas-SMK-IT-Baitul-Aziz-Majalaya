@extends('layouts.master')

@section('title', 'Dashboard Guru')

@section('css')
    <style>
        html,
        body {
            overflow-x: hidden;
        }

        .apexcharts-canvas,
        .apexcharts-svg {
            max-width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb item="Beranda" active="Guru" />

    <div class="row">
        {{-- FILTER BAR --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard.guruMapel') }}" id="filter-form">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-12">
                                <label class="form-label mb-1">Mapel</label>
                                <select class="form-select" name="intrakurikuler_id" id="filter-mapel" required>
                                    @if ($mapels->isEmpty())
                                        <option value="" selected>Tidak ada mapel</option>
                                    @else
                                        @foreach ($mapels as $m)
                                            <option value="{{ $m->intrakurikuler_id }}" @selected($selected && (int) $selected->intrakurikuler_id === (int) $m->intrakurikuler_id)>
                                                {{ $m->nama_kelas }} - {{ $m->nama_pelajaran }} ({{ $m->tahun }}
                                                {{ $m->semester }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            {{-- keep show_all supaya toggle tetap konsisten --}}
                            <input type="hidden" name="show_all" value="{{ $showAll ? 1 : 0 }}">
                        </div>
                    </form>

                    @if ($mapels->isEmpty())
                        <div class="alert alert-warning mt-3 mb-0">
                            Anda belum memiliki mapel yang diampu. Pastikan data <b>intrakurikuler</b> untuk guru ini sudah
                            dibuat.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SECTION: ABSENSI --}}
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0">Ringkasan Kehadiran</h5>
                <span class="text-muted">
                    @if ($selected)
                        {{ $selected->nama_kelas }} • {{ $selected->nama_pelajaran }}
                    @else
                        Berdasarkan kelas & mapel terpilih
                    @endif
                </span>
            </div>
        </div>

        {{-- KPI ABSENSI --}}
        <div class="col-md-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-primary">
                                <i class="ti ti-chart-line f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Kehadiran Rata-rata</h6>
                            <small class="text-muted">Periode terpilih</small>
                        </div>
                    </div>

                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ number_format($kpiAbsensi['rate'] ?? 0, 1) }}%</h4>
                                <p class="mb-0 text-muted"><small>Rata-rata hadir pada periode ini</small></p>
                            </div>
                            <div style="width: 130px">
                                <div id="kpi-absensi-1"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-danger">
                                <i class="ti ti-alert-triangle f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Alpha</h6>
                            <small class="text-muted">Akumulasi periode</small>
                        </div>
                    </div>

                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ (int) ($kpiAbsensi['alpha'] ?? 0) }}</h4>
                                <p class="mb-0 text-muted"><small>Total alpha pada periode ini</small></p>
                            </div>
                            <div style="width: 130px">
                                <div id="kpi-absensi-2"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-12 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-warning">
                                <i class="ti ti-user-exclamation f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Siswa Rawan Absensi</h6>
                            <small class="text-muted">Hadir &lt; {{ (int) ($thresholdRawan * 100) }}%</small>
                        </div>
                    </div>

                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ (int) ($kpiAbsensi['rawan_count'] ?? 0) }} siswa</h4>
                                <p class="text-warning mb-0"><small>Perlu perhatian</small></p>
                            </div>
                            <div style="width: 130px">
                                <div id="kpi-absensi-3"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ABSENSI: STACKED BAR --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Status Kehadiran (Weekly)</h5>
                    </div>

                    <div id="chart-absensi-stacked"></div>

                    <small class="text-muted d-block mt-2">
                        Stacked bar: Hadir, Izin, Sakit, Alpha per minggu.
                    </small>
                </div>
            </div>
        </div>

        {{-- SECTION: NILAI --}}
        <div class="col-12 mt-2">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0">Ringkasan Nilai</h5>
                <span class="text-muted">KKM & tindakan per siswa</span>
            </div>
        </div>

        {{-- KPI NILAI --}}
        <div class="col-md-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-primary">
                                <i class="ti ti-chart-line f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Rata-rata Nilai</h6>
                            <small class="text-muted">Periode terpilih</small>
                        </div>
                    </div>

                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ number_format($kpiNilai['avg'] ?? 0, 1) }}</h4>
                                <p class="mb-0 text-muted"><small>Rata-rata nilai akhir periode ini</small></p>
                            </div>
                            <div style="width: 130px">
                                <div id="kpi-nilai-1"></div>
                            </div>
                        </div>
                    </div>

                    <small class="text-muted d-block mt-2">
                        KKM kelas: <b>{{ (int) $kkm }}</b>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-danger">
                                <i class="ti ti-mood-sad f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Di Bawah KKM</h6>
                            <small class="text-muted">Nilai &lt; {{ (int) $kkm }}</small>
                        </div>
                    </div>

                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ (int) ($kpiNilai['below_kkm'] ?? 0) }} siswa</h4>
                                <p class="mb-0 text-muted"><small>Siswa yang butuh remedial / pendampingan</small></p>
                            </div>
                            <div style="width: 130px">
                                <div id="kpi-nilai-2"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-12 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-success">
                                <i class="ti ti-award f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Top 5 Tertinggi</h6>
                            <small class="text-muted">Urut nilai tertinggi</small>
                        </div>
                    </div>

                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ (int) ($kpiNilai['unggul'] ?? 0) }} siswa</h4>
                                <p class="text-success mb-0"><small>Referensi challenge lanjutan</small></p>
                            </div>
                            <div style="width: 130px">
                                <div id="kpi-nilai-3"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- SEBARAN NILAI --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 pt-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Sebaran Nilai</h5>
                        <span class="text-muted"><small>Bucket: 10</small></span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chart-nilai-distribusi"></div>
                    <small class="text-muted d-block mt-2">
                        X: rentang nilai. Y: jumlah siswa per rentang.
                    </small>
                </div>
            </div>
        </div>

        {{-- LIST NILAI: ATENSI & UNGGUL --}}
        <div class="col-12">
            <div id="nilai-lists"></div>
        </div>

        {{-- BUTUH ATENSI --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body border-bottom pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Butuh Atensi</h5>
                        <span class="text-muted"><small>Nilai &lt; KKM (urut terendah)</small></span>
                    </div>
                    <small class="text-muted d-block mt-1">
                        KKM kelas: <b>{{ (int) $kkm }}</b>
                    </small>
                </div>

                <ul class="list-group list-group-flush">
                    @forelse ($atensiList as $s)
                        <li class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avtar avtar-s border bg-light-danger">
                                        <span>BA</span>
                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <div class="row g-1 align-items-center">
                                        {{-- Nama di kiri --}}
                                        <div class="col-7">
                                            <h6 class="mb-0">{{ $s['nama'] }}</h6>
                                        </div>

                                        {{-- Nilai di kanan --}}
                                        <div class="col-5 text-end">
                                            <p class="text-muted mb-0">
                                                <small>Nilai: {{ number_format($s['nilai'], 1) }}</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item">
                            <div class="text-muted">Tidak ada siswa di bawah KKM pada periode ini.</div>
                        </li>
                    @endforelse
                </ul>

                <div class="card-footer">
                    <div class="d-grid">
                        @if (!$showAll)
                            <a class="btn btn-outline-secondary"
                                href="{{ request()->fullUrlWithQuery(['show_all' => 1]) }}#nilai-lists">
                                Lihat Semua
                            </a>
                        @else
                            <a class="btn btn-outline-secondary"
                                href="{{ request()->fullUrlWithQuery(['show_all' => 0]) }}#nilai-lists">
                                Tampilkan Ringkas
                            </a>
                        @endif
                    </div>
                </div>
            </div>
    </div>


    {{-- UNGGUL (TOP 5) --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body border-bottom pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Top 5 Tertinggi</h5>
                    <span class="text-muted"><small>Urut nilai tertinggi</small></span>
                </div>
            </div>

            <ul class="list-group list-group-flush">
                @forelse ($unggulList as $s)
                    <li class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s border bg-light-success">
                                    <span>UG</span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="row g-1 align-items-center">
                                    <div class="col-7">
                                        <h6 class="mb-0">{{ $s['nama'] }}</h6>
                                    </div>
                                    <div class="col-5 text-end">
                                        <p class="text-muted mb-0">
                                            <small>Nilai: {{ number_format($s['nilai'], 1) }}</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item">
                        <div class="text-muted">Belum ada data nilai pada periode ini.</div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    </div>
@endsection

@section('scripts')
    <script src="/build/js/plugins/apexcharts.min.js"></script>

    <script>
        const chartAbsensi = @json($chartAbsensi);
        const chartNilaiDistribusi = @json($chartNilaiDistribusi);

        let absensiChartInstance = null;
        let nilaiChartInstance = null;

        // Absensi: stacked bar
        const stackedEl = document.querySelector("#chart-absensi-stacked");
        if (stackedEl) {
            absensiChartInstance = new ApexCharts(stackedEl, {
                chart: {
                    type: 'bar',
                    height: 320,
                    stacked: true,
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                series: chartAbsensi.series || [],
                xaxis: {
                    categories: chartAbsensi.categories || []
                }
            });
            absensiChartInstance.render();
        }

        // Nilai: histogram
        const nilaiEl = document.querySelector("#chart-nilai-distribusi");
        if (nilaiEl) {
            nilaiChartInstance = new ApexCharts(nilaiEl, {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                series: [{
                    name: "Jumlah Siswa",
                    data: chartNilaiDistribusi.data || []
                }],
                xaxis: {
                    categories: chartNilaiDistribusi.categories || []
                }
            });
            nilaiChartInstance.render();
        }

        // Auto submit mapel
        const form = document.querySelector('#filter-form');
        const mapelSelect = document.querySelector('#filter-mapel');
        if (form && mapelSelect) {
            mapelSelect.addEventListener('change', () => form.submit());
        }

        // Fix ukuran chart
        const forceReflow = () => {
            window.dispatchEvent(new Event('resize'));
            try {
                absensiChartInstance?.resize();
            } catch (e) {}
            try {
                nilaiChartInstance?.resize();
            } catch (e) {}
        };

        window.addEventListener('load', () => {
            setTimeout(forceReflow, 50);
            setTimeout(forceReflow, 250);
        });
    </script>
@endsection
