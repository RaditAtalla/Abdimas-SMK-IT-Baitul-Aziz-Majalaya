@extends('layouts.master')

@section('title', 'Dashboard Wali Kelas')

@section('content')
  <x-breadcrumb item="Beranda" active="Wali Kelas" />

  <div class="row">
    {{-- FILTER BAR --}}
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <form method="GET" action="{{ route('dashboard.waliKelas') }}" id="form-kelas">
            <div class="row g-3">
  <div class="col-12">
    <label class="form-label mb-1">Kelas (Wali)</label>

    <select class="form-select" name="kelas_ajar_id" id="kelas-ajar" required>
      @forelse($kelasAjars as $ka)
        <option value="{{ $ka->kelas_ajar_id }}"
          @selected(optional($selectedKelasAjar)->kelas_ajar_id == $ka->kelas_ajar_id)>
          {{ $ka->nama_kelas }} • {{ $ka->tahun }} ({{ $ka->semester }})
        </option>
      @empty
        <option selected>Belum ada kelas</option>
      @endforelse
    </select>

    {{-- keep show_all state --}}
    <input type="hidden" name="show_all" value="{{ $showAll ? 1 : 0 }}">

    {{-- info bawah select --}}
    @if($selectedKelasAjar)
      <div class="d-flex align-items-center   flex-wrap gap-2 mt-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="badge bg-light-primary">
            KKM Kelas: <b>{{ (int) $kkm }}</b>
          </span>
        </div>

        <span class="badge bg-light-secondary">
          {{ $selectedKelasAjar->tahun }} ({{ $selectedKelasAjar->semester }})
        </span>
      </div>
    @endif
  </div>
</div>

          </form>

          @if($kelasAjars->isEmpty())
            <div class="alert alert-warning mt-3 mb-0">
              Anda belum menjadi wali kelas.
            </div>
          @endif
        </div>
      </div>
    </div>

    @if (!$selectedKelasAjar)
      {{-- no content --}}
    @else
      {{-- HEADER --}}
      <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
          <h5 class="mb-0">Dashboard Wali: {{ $selectedKelasAjar->nama_kelas }}</h5>
          <div class="text-muted">
            <span>{{ $selectedKelasAjar->tahun }} ({{ $selectedKelasAjar->semester }})</span>
            <span class="mx-2">•</span>
            <span>KKM: <b>{{ (int) $kkm }}</b></span>
          </div>
        </div>
      </div>

      {{-- KPI --}}
      <div class="col-md-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avtar avtar-s bg-light-primary">
                <i class="ti ti-chart-line f-20"></i>
              </div>
              <div class="ms-3">
                <h6 class="mb-0">Rerata Nilai Kelas</h6>
                <small class="text-muted">Semua mapel</small>
              </div>
            </div>
            <div class="mt-3">
              <h3 class="mb-0">{{ number_format($kpi['avg_nilai_kelas'] ?? 0, 1) }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avtar avtar-s bg-light-success">
                <i class="ti ti-award f-20"></i>
              </div>
              <div class="ms-3">
                <h6 class="mb-0">Unggul</h6>
                <small class="text-muted">Avg siswa ≥ 85</small>
              </div>
            </div>
            <div class="mt-3">
              <h3 class="mb-0">{{ (int) ($kpi['unggul_count'] ?? 0) }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avtar avtar-s bg-light-danger">
                <i class="ti ti-user-exclamation f-20"></i>
              </div>
              <div class="ms-3">
                <h6 class="mb-0">Butuh Atensi</h6>
                <small class="text-muted">Avg siswa &lt; KKM</small>
              </div>
            </div>
            <div class="mt-3">
              <h3 class="mb-0">{{ (int) ($kpi['atensi_count'] ?? 0) }}</h3>
              <small class="text-muted">KKM: {{ (int) $kkm }}</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avtar avtar-s bg-light-warning">
                <i class="ti ti-checklist f-20"></i>
              </div>
              <div class="ms-3">
                <h6 class="mb-0">Kehadiran</h6>
                <small class="text-muted">Semua mapel</small>
              </div>
            </div>
            <div class="mt-3">
              <h3 class="mb-0">{{ number_format($kpi['hadir_rate'] ?? 0, 1) }}%</h3>
              <small class="text-muted">
                Alpha total: {{ (int) ($kpi['alpha_total'] ?? 0) }}
              </small>
            </div>
          </div>
        </div>
      </div>

      {{-- DISTRIBUSI NILAI --}}
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Distribusi Rata-rata Nilai Siswa</h5>
            <small class="text-muted">Histogram avg nilai per siswa (semua mapel) • bucket 10</small>
          </div>
          <div class="card-body">
            <div id="chart-distribusi" style="min-height: 320px; overflow: hidden;"></div>
          </div>
        </div>
      </div>

      {{-- BUTUH ATENSI (TOP 5 / ALL) --}}
      <div class="col-lg-5" id="atensi-list">
        <div class="card">
          <div class="card-body border-bottom pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div>
                <h5 class="mb-0">Butuh Atensi</h5>
                <small class="text-muted d-block mt-1">Avg siswa &lt; KKM (urut terendah)</small>
              </div>
              <span class="badge bg-light-danger">KKM: <b>{{ (int) $kkm }}</b></span>
            </div>
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
                      <div class="col-7">
                        <h6 class="mb-0">{{ $s['nama'] }}</h6>
                      </div>
                      <div class="col-5 text-end">
                        <p class="text-muted mb-0">
                          <small>Avg: <b>{{ number_format($s['avg'], 1) }}</b></small>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            @empty
              <li class="list-group-item">
                <div class="text-muted">Tidak ada siswa di bawah KKM pada semester ini.</div>
              </li>
            @endforelse
          </ul>

          <div class="card-footer">
            <div class="d-grid">
              @if (!$showAll)
                <a class="btn btn-outline-secondary"
                   href="{{ request()->fullUrlWithQuery(['show_all' => 1]) }}#atensi-list">
                  Lihat Semua
                </a>
              @else
                <a class="btn btn-outline-secondary"
                   href="{{ request()->fullUrlWithQuery(['show_all' => 0]) }}#atensi-list">
                  Tampilkan Ringkas
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>

      {{-- ROW: MAPEL RENDAH + KEHADIRAN (50/50) --}}
      <div class="col-12">
        <div class="row g-3">
          {{-- MAPEL RENDAH PER SISWA --}}
          <div class="col-lg-6" id="low-mapel">
            <div class="card h-100">
              <div class="card-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div>
                    <h5 class="mb-0">Mapel Rendah per Siswa</h5>
                    <small class="text-muted">
                      Mapel dengan <b>avg &lt; KKM</b> beserta daftar mapel & nilai.
                    </small>
                  </div>
                  <span class="badge bg-light-primary">KKM: <b>{{ (int) $kkm }}</b></span>
                </div>
              </div>

              <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 420px; overflow: auto;">
                  <table class="table table-hover mb-0 w-100">
                    <thead class="sticky-top bg-white">
                      <tr>
                        <th style="min-width: 160px">Nama</th>
                        <th class="text-end" style="width: 120px">Jml</th>
                        <th style="min-width: 260px">Detail</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($lowSubjectList as $r)
                        <tr>
                          <td class="fw-semibold">{{ $r['nama'] }}</td>
                          <td class="text-end">
                            <span class="badge bg-light-danger">{{ (int) $r['low_count'] }}</span>
                          </td>
                          <td>
                            @if(empty($r['subjects']))
                              <span class="text-muted">-</span>
                            @else
                              <div class="d-flex flex-wrap gap-2">
                                @foreach($r['subjects_preview'] as $s)
                                  <span class="badge bg-light-warning">
                                    {{ $s['nama_pelajaran'] }}: <b>{{ number_format($s['avg'], 1) }}</b>
                                  </span>
                                @endforeach

                                @if(($r['subjects_hidden_count'] ?? 0) > 0)
                                  <span class="badge bg-light-secondary">
                                    +{{ (int) $r['subjects_hidden_count'] }} lagi
                                  </span>
                                @endif
                              </div>
                            @endif
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="3" class="text-muted text-center py-3">
                            Belum ada data nilai mapel di bawah KKM pada semester ini.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="card-footer">
                <div class="d-grid">
                  @if (!$showAll)
                    <a class="btn btn-outline-secondary"
                       href="{{ request()->fullUrlWithQuery(['show_all' => 1]) }}#low-mapel">
                      Lihat Semua
                    </a>
                  @else
                    <a class="btn btn-outline-secondary"
                       href="{{ request()->fullUrlWithQuery(['show_all' => 0]) }}#low-mapel">
                      Tampilkan Ringkas
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </div>

          {{-- KEHADIRAN (ringkas biar muat 50%) --}}
          <div class="col-lg-6" id="attendance">
            <div class="card h-100">
              <div class="card-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div>
                    <h5 class="mb-0">Kehadiran Siswa</h5>
                    <small class="text-muted">
                      Prioritas: <b>Hadir% terendah</b> (agregat semua mapel)
                    </small>
                  </div>
                  <span class="badge bg-light-secondary">
                    {{ $showAll ? 'Semua siswa' : 'Top 10 prioritas' }}
                  </span>
                </div>
              </div>

              <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 420px; overflow: auto;">
                  <table class="table table-hover mb-0 w-100">
                    <thead class="sticky-top bg-white">
                      <tr>
                        <th style="min-width: 160px">Nama</th>
                        <th class="text-end">Hadir%</th>
                        <th class="text-end">Alpha</th>
                        <th class="text-end">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($attendanceList as $x)
                        <tr>
                          <td class="fw-semibold">{{ $x['nama'] }}</td>
                          <td class="text-end fw-semibold">{{ number_format($x['hadir_pct'], 1) }}%</td>
                          <td class="text-end fw-semibold text-danger">{{ (int) $x['alpha'] }}</td>
                          <td class="text-end text-muted">{{ (int) $x['total'] }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="4" class="text-muted text-center py-3">
                            Belum ada data absensi pada semester ini.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="card-footer">
                <div class="d-grid">
                  @if (!$showAll)
                    <a class="btn btn-outline-secondary"
                       href="{{ request()->fullUrlWithQuery(['show_all' => 1]) }}#attendance">
                      Lihat Semua
                    </a>
                  @else
                    <a class="btn btn-outline-secondary"
                       href="{{ request()->fullUrlWithQuery(['show_all' => 0]) }}#attendance">
                      Tampilkan Ringkas
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    @endif
  </div>
@endsection

@section('scripts')
  <script src="/build/js/plugins/apexcharts.min.js"></script>

  <script>
    // auto submit kelas
    const kelasSel = document.querySelector('#kelas-ajar');
    const formKelas = document.querySelector('#form-kelas');
    if (kelasSel && formKelas) {
      kelasSel.addEventListener('change', () => formKelas.submit());
    }

    const distribusi = @json($chartDistribusi);
    const chartEl = document.querySelector("#chart-distribusi");
    let chartDistribusiInstance = null;

    function renderDistribusiChart() {
      if (!chartEl) return;

      if (chartDistribusiInstance) {
        chartDistribusiInstance.destroy();
        chartDistribusiInstance = null;
      }

      chartDistribusiInstance = new ApexCharts(chartEl, {
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        dataLabels: { enabled: false },
        series: [{ name: "Jumlah Siswa", data: distribusi.data || [] }],
        xaxis: { categories: distribusi.categories || [] }
      });

      chartDistribusiInstance.render().then(() => {
        setTimeout(() => chartDistribusiInstance?.resize(), 150);
      });
    }

    document.addEventListener('DOMContentLoaded', () => setTimeout(renderDistribusiChart, 50));
    window.addEventListener('load', () => setTimeout(renderDistribusiChart, 0));
    window.addEventListener('resize', () => chartDistribusiInstance?.resize());
  </script>
@endsection
