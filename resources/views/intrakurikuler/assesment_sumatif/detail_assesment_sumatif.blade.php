@extends('layouts.master')

@section('title', 'Detail Asesmen Sumatif')

@section('css')
  <link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection

@section('content')

  <x-breadcrumb item="Intrakurikuler" subItem="Intrakurikuler" subLink="{{ route('intrakurikuler.index') }}" sub2Item="Asesmen Sumatif" sub2Link="{{ route('assesment-sumatif.index', $intrakurikuler->intrakurikuler_id) }}" active="Detail Asesmen Sumatif" />

  <div class="row">
    <div class="col-md-12">
      <div class="card">

        {{-- Header --}}
        <div class="card-header">
          <h5 class="fs-4 mb-0">
            {{ $riwayatKelas->siswa?->user?->name ?? $riwayatKelas->siswa?->nama ?? '-' }}
          </h5>
          <span class="d-block m-t-5">
            {{ $intrakurikuler->nama_pelajaran }}
            •
            {{ $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '-' }}
          </span>
        </div>

        <div class="card-body">

          <form method="POST"
            action="{{ $hasExistingScores
              ? route('assesment_sumatif.detail.update', ['intrakurikuler' => $intrakurikuler->intrakurikuler_id, 'riwayatKelas' => $riwayatKelas->riwayat_kelas_id])
              : route('assesment_sumatif.detail.store',  ['intrakurikuler' => $intrakurikuler->intrakurikuler_id, 'riwayatKelas' => $riwayatKelas->riwayat_kelas_id]) }}">

            @csrf
            @if($hasExistingScores)
              @method('PUT')
            @endif

            <div class="row">

              {{-- CONTAINER 1 --}}
              <div class="col-lg-6 mb-3">
                <div class="card h-100">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                      <h5 class="mb-0">Sumatif Akhir Lingkup Materi</h5>
                      <span class="d-block m-t-5">Isi nilai per lingkup materi</span>
                    </div>
                    <span class="badge bg-light-primary">Total: {{ $totalLingkup ?? '-' }}</span>
                  </div>

                  <div class="card-body">
                    <div class="row">

                      @forelse($asesmenLingkup as $i => $a)
                        <div class="mb-4 col-md-6">
                          <label class="form-label fs-6 f-w-600">LM {{ $i + 1 }}</label>
                          <p class="mb-2 text-muted">
                            {{ $a->lingkupMateri?->nama_materi ?? 'Lingkup materi belum di-set' }}
                          </p>

                          <input type="number"
                            class="form-control @error("scores.$a->asesmen_sumatif_id") is-invalid @enderror"
                            placeholder="Nilai (0-100)"
                            name="scores[{{ $a->asesmen_sumatif_id }}]"
                            value="{{ old("scores.$a->asesmen_sumatif_id", $skor[$a->asesmen_sumatif_id]->nilai ?? '') }}"
                            min="0" max="100">

                          @error("scores.$a->asesmen_sumatif_id")
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      @empty
                        <div class="col-12">
                          <div class="alert alert-warning mb-0">
                            Belum ada asesmen tipe <b>sumatif_lingkup</b> untuk mapel ini.
                          </div>
                        </div>
                      @endforelse

                    </div>
                  </div>
                </div>
              </div>

              {{-- CONTAINER 2 --}}
              <div class="col-lg-6 mb-3">
                <div class="card h-100">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                      <h5 class="mb-0">Sumatif Akhir Semester</h5>
                      <span class="d-block m-t-5">Isi nilai komponen akhir semester</span>
                    </div>
                    <span class="badge bg-light-primary">Total: {{ $totalSemester ?? '-' }}</span>
                  </div>

                  <div class="card-body">
                    <div class="row">

                      @forelse($asesmenSemester as $a)
                        @php
                          $label = match ($a->asesmen_type) {
                            'non_test' => 'Non test',
                            'test' => 'Test',
                            default => strtoupper(str_replace('_', ' ', $a->asesmen_type)),
                          };
                          $desc = match ($a->asesmen_type) {
                            'non_test' => 'Nilai akhir non test',
                            'test' => 'Nilai akhir test',
                            default => 'Nilai akhir',
                          };
                        @endphp

                        <div class="mb-4 col-md-6">
                          <label class="form-label fs-6 f-w-600">{{ $label }}</label>
                          <p class="mb-2 text-muted">{{ $desc }}</p>

                          <input type="number"
                            class="form-control @error("scores.$a->asesmen_sumatif_id") is-invalid @enderror"
                            placeholder="Nilai (0-100)"
                            name="scores[{{ $a->asesmen_sumatif_id }}]"
                            value="{{ old("scores.$a->asesmen_sumatif_id", $skor[$a->asesmen_sumatif_id]->nilai ?? '') }}"
                            min="0" max="100">

                          @error("scores.$a->asesmen_sumatif_id")
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      @empty
                        <div class="col-12">
                          <div class="alert alert-warning mb-0">
                            Belum ada asesmen tipe <b>non_test/test</b> untuk mapel ini.
                          </div>
                        </div>
                      @endforelse

                    </div>
                  </div>
                </div>
              </div>

              {{-- RINGKASAN --}}
              <div class="col-12">
                <div class="card">
                  <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap gap-3">
                      <div class="px-3 py-2 border rounded">
                        <div class="text-muted small">Total Akhir Lingkup Materi</div>
                        <div class="fs-5 f-w-600">{{ $totalLingkup ?? '-' }}</div>
                      </div>

                      <div class="px-3 py-2 border rounded">
                        <div class="text-muted small">Total Akhir Semester</div>
                        <div class="fs-5 f-w-600">{{ $totalSemester ?? '-' }}</div>
                      </div>

                      <div class="px-3 py-2 border rounded">
                        <div class="text-muted small">Nilai Rapor</div>
                        <div class="fs-4 f-w-700 text-primary">{{ $nilaiRapor ?? '-' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- ACTIONS --}}
              <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>

                <a href="{{ route('assesment-sumatif.index', ['intrakurikuler' => $intrakurikuler->intrakurikuler_id]) }}"
                  class="btn btn-light-secondary">
                  Kembali
                </a>
              </div>

              {{-- NAV SISWA --}}
              <div class="col-12">
                <div class="d-flex justify-content-end gap-5 mt-1">

                  @if($prevRiwayat)
                    <a href="{{ route('assesment_sumatif.detail', [
                      'intrakurikuler' => $intrakurikuler->intrakurikuler_id,
                      'riwayatKelas' => $prevRiwayat->riwayat_kelas_id,
                    ]) }}" class="link-primary">
                      <i class="bi bi-chevron-left"></i>
                      {{ $prevRiwayat->siswa?->user?->name ?? $prevRiwayat->siswa?->nama ?? '-' }}
                    </a>
                  @else
                    <span class="text-muted">
                      <i class="bi bi-chevron-left"></i> -
                    </span>
                  @endif

                  @if($nextRiwayat)
                    <a href="{{ route('assesment_sumatif.detail', [
                      'intrakurikuler' => $intrakurikuler->intrakurikuler_id,
                      'riwayatKelas' => $nextRiwayat->riwayat_kelas_id,
                    ]) }}" class="link-primary">
                      {{ $nextRiwayat->siswa?->user?->name ?? $nextRiwayat->siswa?->nama ?? '-' }}
                      <i class="bi bi-chevron-right"></i>
                    </a>
                  @else
                    <span class="text-muted">
                      - <i class="bi bi-chevron-right"></i>
                    </span>
                  @endif

                </div>
              </div>

            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

@endsection

@section('scripts')
  <script type="module">
    import { DataTable } from '/build/js/plugins/module.js';
    // halaman ini tidak punya tabel #pc-dt-simple
  </script>
@endsection
