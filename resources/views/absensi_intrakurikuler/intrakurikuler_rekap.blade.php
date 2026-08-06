@extends('layouts.master')

@section('title', 'Rekap Absensi (Intrakurikuler)')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
    <link rel="stylesheet" href="/build/css/plugins/flatpickr.min.css" />
@endsection

@section('content')
    <x-breadcrumb item="Absensi Intrakurikuler" link="{{ route('absensi.intrakurikuler.list') }}" active="Rekap Absensi" />

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">
                                Rekap Absensi - {{ $intrakurikuler->nama_pelajaran }}
                            </h5>
                            <small class="text-muted">
                                {{ $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '-' }} •
                                {{ $intrakurikuler->kelasAjar?->tahunAjaran?->tahun ?? '-' }}
                                {{ $intrakurikuler->kelasAjar?->tahunAjaran?->semester ?? '' }}
                                • <span class="ms-1">Read-only (ubah di Absen Harian)</span>
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('absensi.intrakurikuler.harian', ['intrakurikuler' => $intrakurikuler->intrakurikuler_id]) }}"
                                class="btn btn-outline-secondary">
                                Absen Harian
                            </a>
                        </div>
                    </div>

                    {{-- Filter tanggal / range (1 input + 1 tombol) --}}
                    <div class="row g-2 mt-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1">Tanggal / Range</label>
                            <input type="text" id="rangeDate" class="form-control"
                                placeholder="Klik 1x untuk 1 tanggal, 2x untuk range" autocomplete="off" />
                            <input type="hidden" id="dateFrom" value="{{ $from }}">
                            <input type="hidden" id="dateTo" value="{{ $to }}">
                            <small class="text-muted d-block mt-1">
                                1 tanggal = rekap hari itu. 2 tanggal = rekap range.
                            </small>
                        </div>

                        <div class="col-12 col-md-6 d-flex flex-column">
                            <label class="form-label mb-1">&nbsp;</label> {{-- spacer biar sejajar --}}
                            <div class="d-flex align-items-end gap-2">
                                <button type="button" id="btnApply" class="btn btn-primary">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Total Hadir</th>
                                    <th>Total Alpha</th>
                                    <th>Total Sakit</th>
                                    <th>Total Izin</th>
                                    <th>Persentase Hadir</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($rows as $r)
                                    @php
                                        $total =
                                            ($r['hadir'] ?? 0) +
                                            ($r['alpha'] ?? 0) +
                                            ($r['sakit'] ?? 0) +
                                            ($r['izin'] ?? 0);
                                        $persen =
                                            $total > 0
                                                ? $r['persen'] ?? round((($r['hadir'] ?? 0) / $total) * 100, 1)
                                                : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img src="{{ $r['avatar'] ?? '/build/images/user/avatar-1.jpg' }}"
                                                        alt="user image" class="img-radius wid-40" />
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">{{ $r['name'] ?? '-' }}</h6>
                                                    <small class="text-muted">
                                                        {{ $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '-' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ (int) ($r['hadir'] ?? 0) }}</td>
                                        <td>{{ (int) ($r['alpha'] ?? 0) }}</td>
                                        <td>{{ (int) ($r['sakit'] ?? 0) }}</td>
                                        <td>{{ (int) ($r['izin'] ?? 0) }}</td>

                                        <td>
                                            <span class="badge bg-light-primary">
                                                {{ $persen }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                    <div class="mt-3 ps-4">
                        <a href="{{ route('absensi.intrakurikuler.list') }}" class="btn btn-light-secondary px-3">
                            Kembali
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- DataTable --}}
    <script type="module">
        import {
            DataTable
        } from '/build/js/plugins/module.js';
        window.dt = new DataTable('#pc-dt-simple');
    </script>

    {{-- Flatpickr --}}
    <script src="/build/js/plugins/flatpickr.min.js"></script>

    <script>
        const rangeInput = document.getElementById('rangeDate');
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');
        const btnApply = document.getElementById('btnApply');

        const fp = flatpickr(rangeInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            maxDate: 'today',
            // default dari controller
            defaultDate: [dateFrom.value || null, dateTo.value || null].filter(Boolean),

            onChange: function(selectedDates) {
                // ✅ FIX: kalau user pilih 1 tanggal, set from=to
                if (selectedDates.length === 1) {
                    const v = fp.formatDate(selectedDates[0], 'Y-m-d');
                    dateFrom.value = v;
                    dateTo.value = v;
                    return;
                }

                if (selectedDates.length >= 2) {
                    dateFrom.value = fp.formatDate(selectedDates[0], 'Y-m-d');
                    dateTo.value = fp.formatDate(selectedDates[1], 'Y-m-d');
                }
            }
        });

        btnApply.addEventListener('click', () => {
            const params = new URLSearchParams(window.location.search);

            if (dateFrom.value) params.set('from', dateFrom.value);
            else params.delete('from');

            if (dateTo.value) params.set('to', dateTo.value);
            else params.delete('to');

            const baseUrl = window.location.pathname;
            const qs = params.toString();
            window.location.href = qs ? `${baseUrl}?${qs}` : baseUrl;
        });
    </script>
@endsection
