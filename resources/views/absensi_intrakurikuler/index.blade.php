@extends('layouts.master')

@section('title', 'Absen Harian (Intrakurikuler)')

@section('css')
<link rel="stylesheet" href="/build/css/plugins/style.css" />
<link rel="stylesheet" href="/build/css/plugins/flatpickr.min.css" />
@endsection

@section('content')
<x-breadcrumb item="Absensi Intrakurikuler" link="{{ route('absensi.intrakurikuler.list') }}" active="Absen Harian" />

<div class="row">
  <div class="col-12">
    <div class="card table-card">
      <div class="card-header">
        <div class="d-sm-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-1">Absen Harian - {{ $intrakurikuler->nama_pelajaran }}</h5>
            <small class="text-muted">
              {{ $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '-' }} •
              {{ $intrakurikuler->kelasAjar?->tahunAjaran?->tahun ?? '-' }}
              {{ $intrakurikuler->kelasAjar?->tahunAjaran?->semester ?? '' }}
            </small>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" form="absensiForm" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Simpan Absensi
            </button>
            <a href="{{ route('absensi.intrakurikuler.rekap', $intrakurikuler->intrakurikuler_id) }}"
              class="btn btn-outline-secondary">
              Rekap Absensi
            </a>
          </div>
        </div>

        <div class="row g-2 align-items-end mt-3">
          <div class="col-12 col-md-4">
            <label class="form-label mb-1">Tanggal Absensi</label>
            <input type="text" id="pickDate" class="form-control" placeholder="Pilih tanggal" autocomplete="off" />
            <input type="hidden" id="dateSelected" value="{{ $selectedDate }}">
          </div>
        </div>
      </div>

      <div class="card-body pt-3">
        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <form id="absensiForm" method="POST" action="{{ route('absensi.intrakurikuler.harian.store', $intrakurikuler->intrakurikuler_id) }}">
          @csrf
          <div id="hiddenInputsContainer"></div>

          <div class="table-responsive">
            <table class="table table-hover" id="pc-dt-simple">
              <thead>
                <tr>
                  <th style="width: 25%;">Nama</th>
                  <th style="width: 15%;">Status</th>
                  <th style="width: 25%;">Absensi</th>
                  <th style="width: 35%;">Keterangan</th>
                </tr>
              </thead>

              <tbody>
                @foreach ($students as $s)
                @php
                  $att = $attendanceMap->get($s['riwayat_kelas_id']);
                  $currentStatus = $att?->status ?? '';
                  $currentNote = $att?->note ?? '';
                @endphp

                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <img src="{{ $s['avatar'] }}" alt="user" class="img-radius wid-40" />
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ $s['name'] }}</h6>
                        <small class="text-muted">{{ $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '-' }}</small>
                      </div>
                    </div>
                  </td>

                  <td>
                    @if($att)
                      @php
                        $badgeClass = match($att->status) {
                            'hadir' => 'bg-light-success text-success',
                            'alpha' => 'bg-light-danger text-danger',
                            'sakit' => 'bg-light-warning text-warning',
                            'izin'  => 'bg-light-info text-info',
                            default => 'bg-light-primary text-primary',
                        };
                      @endphp
                      <span class="badge {{ $badgeClass }} text-capitalize">{{ $att->status }}</span>
                    @else
                      <span class="badge bg-light-secondary">Belum</span>
                    @endif
                  </td>

                  <td>
                    <div class="d-flex gap-2">
                      <div class="form-check">
                        <input class="form-check-input absensi-radio" type="radio"
                          name="status_{{ $s['riwayat_kelas_id'] }}"
                          id="status_hadir_{{ $s['riwayat_kelas_id'] }}"
                          data-rk-id="{{ $s['riwayat_kelas_id'] }}"
                          value="hadir" {{ $currentStatus === 'hadir' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_hadir_{{ $s['riwayat_kelas_id'] }}">Hadir</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input absensi-radio" type="radio"
                          name="status_{{ $s['riwayat_kelas_id'] }}"
                          id="status_alpha_{{ $s['riwayat_kelas_id'] }}"
                          data-rk-id="{{ $s['riwayat_kelas_id'] }}"
                          value="alpha" {{ $currentStatus === 'alpha' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_alpha_{{ $s['riwayat_kelas_id'] }}">Alpha</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input absensi-radio" type="radio"
                          name="status_{{ $s['riwayat_kelas_id'] }}"
                          id="status_sakit_{{ $s['riwayat_kelas_id'] }}"
                          data-rk-id="{{ $s['riwayat_kelas_id'] }}"
                          value="sakit" {{ $currentStatus === 'sakit' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_sakit_{{ $s['riwayat_kelas_id'] }}">Sakit</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input absensi-radio" type="radio"
                          name="status_{{ $s['riwayat_kelas_id'] }}"
                          id="status_izin_{{ $s['riwayat_kelas_id'] }}"
                          data-rk-id="{{ $s['riwayat_kelas_id'] }}"
                          value="izin" {{ $currentStatus === 'izin' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_izin_{{ $s['riwayat_kelas_id'] }}">Izin</label>
                      </div>
                    </div>
                  </td>

                  <td>
                    <input type="text" class="form-control form-control-sm absensi-note"
                      data-rk-id="{{ $s['riwayat_kelas_id'] }}"
                      value="{{ $currentNote }}"
                      placeholder="Keterangan (opsional)...">
                  </td>
                </tr>
                @endforeach
              </tbody>

            </table>
          </div>
        </form>

        <div class="mt-3 ps-2">
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
<script type="module">
  import { DataTable } from '/build/js/plugins/module.js';
  window.dt = new DataTable('#pc-dt-simple');
</script>

<script src="/build/js/plugins/flatpickr.min.js"></script>

<script>
  // ====== DATE PICKER (auto request) ======
  const pickDate = document.getElementById('pickDate');
  const dateSelected = document.getElementById('dateSelected');

  function redirectWithDate(dateStr) {
    const params = new URLSearchParams(window.location.search);
    if (dateStr) params.set('date', dateStr);
    else params.delete('date');

    const target = `${window.location.pathname}?${params.toString()}`;
    if (target !== window.location.href) window.location.href = target;
  }

  const fp = flatpickr(pickDate, {
    mode: 'single',
    dateFormat: 'Y-m-d',
    defaultDate: dateSelected.value || null,
    maxDate: 'today',
    onChange: function(selectedDates) {
      if (!selectedDates.length) return;
      const dateStr = fp.formatDate(selectedDates[0], 'Y-m-d');

      const cur = new URLSearchParams(window.location.search).get('date') || '';
      if (dateStr === cur) return;

      dateSelected.value = dateStr;
      redirectWithDate(dateStr);
    }
  });

  if (dateSelected.value) {
    fp.setDate(dateSelected.value, false);
  }

  // ====== ATTENDANCE STATE MANAGEMENT ACROSS PAGINATION ======
  const attendanceState = {};

  // Initialize attendanceState from initial PHP render (keyed strictly by student riwayat_kelas_id as String)
  @foreach ($students as $s)
    @php $att = $attendanceMap->get($s['riwayat_kelas_id']); @endphp
    attendanceState[String("{{ $s['riwayat_kelas_id'] }}")] = {
      status: {!! json_encode($att?->status ?? '') !!},
      note: {!! json_encode($att?->note ?? '') !!}
    };
  @endforeach

  function restoreCurrentPageInputs() {
    // Restore Radio Buttons for visible rows on the active page
    document.querySelectorAll('.absensi-radio').forEach(radio => {
      const rkId = String(radio.getAttribute('data-rk-id') || '');
      if (!rkId || !attendanceState[rkId]) return;

      const savedStatus = attendanceState[rkId].status || '';
      if (savedStatus) {
        radio.checked = (radio.value === savedStatus);
      } else {
        radio.checked = false;
      }
    });

    // Restore Note Inputs for visible rows on the active page
    document.querySelectorAll('.absensi-note').forEach(input => {
      const rkId = String(input.getAttribute('data-rk-id') || '');
      if (!rkId || !attendanceState[rkId]) return;

      const savedNote = attendanceState[rkId].note !== undefined ? attendanceState[rkId].note : '';
      input.value = savedNote;
    });
  }

  // Listen to user changes (using closest for reliable capture)
  document.addEventListener('change', function(e) {
    const radio = e.target.closest('.absensi-radio');
    if (!radio) return;

    const rkId = String(radio.getAttribute('data-rk-id') || '');
    if (!rkId) return;

    if (!attendanceState[rkId]) {
      attendanceState[rkId] = { status: '', note: '' };
    }
    attendanceState[rkId].status = radio.value;
  });

  document.addEventListener('input', function(e) {
    const input = e.target.closest('.absensi-note');
    if (!input) return;

    const rkId = String(input.getAttribute('data-rk-id') || '');
    if (!rkId) return;

    if (!attendanceState[rkId]) {
      attendanceState[rkId] = { status: '', note: '' };
    }
    attendanceState[rkId].note = input.value;
  });

  // Schedule restoration asynchronously so Simple-DataTables has completely finished rendering the new DOM page
  function triggerRestoration() {
    setTimeout(restoreCurrentPageInputs, 0);
    setTimeout(restoreCurrentPageInputs, 50);
  }

  // 1. Observe DOM mutations in tbody
  const tbody = document.querySelector('#pc-dt-simple tbody');
  if (tbody) {
    const observer = new MutationObserver(function() {
      triggerRestoration();
    });
    observer.observe(tbody, { childList: true, subtree: true });
  }

  // 2. Listen to clicks on pagination & headers (sorting)
  document.addEventListener('click', function(e) {
    if (e.target.closest('.datatable-pagination') || e.target.closest('.datatable-selector') || e.target.closest('#pc-dt-simple thead')) {
      triggerRestoration();
    }
  });

  // 3. Attach directly to DataTable instance events if available
  function attachDtListeners() {
    if (window.dt) {
      window.dt.on('datatable.page', triggerRestoration);
      window.dt.on('datatable.sort', triggerRestoration);
      window.dt.on('datatable.search', triggerRestoration);
      window.dt.on('datatable.perpage', triggerRestoration);
    } else {
      setTimeout(attachDtListeners, 100);
    }
  }
  attachDtListeners();

  // Initial trigger
  triggerRestoration();

  // ====== FORM SUBMISSION ======
  const absensiForm = document.getElementById('absensiForm');
  if (absensiForm) {
    absensiForm.addEventListener('submit', function(e) {
      // Sync currently visible inputs before submit
      document.querySelectorAll('.absensi-radio:checked').forEach(radio => {
        const rkId = String(radio.getAttribute('data-rk-id') || '');
        if (rkId) {
          if (!attendanceState[rkId]) attendanceState[rkId] = {};
          attendanceState[rkId].status = radio.value;
        }
      });
      document.querySelectorAll('.absensi-note').forEach(input => {
        const rkId = String(input.getAttribute('data-rk-id') || '');
        if (rkId) {
          if (!attendanceState[rkId]) attendanceState[rkId] = {};
          attendanceState[rkId].note = input.value;
        }
      });

      const container = document.getElementById('hiddenInputsContainer');
      container.innerHTML = '';

      const hiddenDate = document.createElement('input');
      hiddenDate.type = 'hidden';
      hiddenDate.name = 'tanggal';
      hiddenDate.value = dateSelected.value || '{{ $selectedDate }}';
      container.appendChild(hiddenDate);

      let idx = 0;
      for (const [rkId, val] of Object.entries(attendanceState)) {
        if (val && val.status) {
          const inputRk = document.createElement('input');
          inputRk.type = 'hidden';
          inputRk.name = `attendances[${idx}][riwayat_kelas_id]`;
          inputRk.value = rkId;
          container.appendChild(inputRk);

          const inputStatus = document.createElement('input');
          inputStatus.type = 'hidden';
          inputStatus.name = `attendances[${idx}][status]`;
          inputStatus.value = val.status;
          container.appendChild(inputStatus);

          const inputNote = document.createElement('input');
          inputNote.type = 'hidden';
          inputNote.name = `attendances[${idx}][note]`;
          inputNote.value = val.note || '';
          container.appendChild(inputNote);

          idx++;
        }
      }
    });
  }
</script>
@endsection
