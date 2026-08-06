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

        <div class="table-responsive">
          <table class="table table-hover" id="pc-dt-simple">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($students as $s)
              @php
                $att = $attendanceMap->get($s['riwayat_kelas_id']);
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
                    <span class="badge bg-light-primary text-capitalize">{{ $att->status }}</span>
                  @else
                    <span class="badge bg-light-secondary">Belum</span>
                  @endif
                </td>

                <td><span class="text-muted">{{ $att?->note ?: '-' }}</span></td>

                <td class="text-end">
                  <button
                    type="button"
                    class="btn {{ $att ? 'btn-outline-primary' : 'btn-primary' }} btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#absenModal"
                    data-mode="{{ $att ? 'edit' : 'create' }}"
                    data-riwayat-kelas-id="{{ $s['riwayat_kelas_id'] }}"
                    data-student-name="{{ $s['name'] }}"
                    data-status="{{ $att?->status ?? '' }}"
                    data-note="{{ $att?->note ?? '' }}">
                    {{ $att ? 'Edit' : 'Absensi' }}
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>

          </table>
        </div>

        <div class="mt-3 ps-2">
          <a href="{{ route('absensi.intrakurikuler.list') }}" class="btn btn-light-secondary px-3">
            Kembali
          </a>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="absenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('absensi.intrakurikuler.harian.store', $intrakurikuler->intrakurikuler_id) }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title" id="absenModalTitle">Absensi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="tanggal" id="mTanggal">
          <input type="hidden" name="riwayat_kelas_id" id="mRiwayatKelasId">

          <div class="mb-2">
            <label class="form-label mb-1">Siswa</label>
            <input type="text" class="form-control" id="mStudentName" readonly>
          </div>

          <div class="mb-2">
            <label class="form-label mb-1">Status</label>
            <select class="form-select" name="status" id="mStatus" required>
              <option value="">Pilih status</option>
              <option value="hadir">Hadir</option>
              <option value="alpha">Alpha</option>
              <option value="sakit">Sakit</option>
              <option value="izin">Izin</option>
            </select>
          </div>

          <div class="mb-0 d-none" id="noteWrapper">
            <label class="form-label mb-1">Keterangan</label>
            <textarea class="form-control" name="note" id="mNote" rows="2"
              placeholder="Contoh: demam / izin keluarga / dsb"></textarea>
            <small class="text-muted">Wajib diisi untuk status Izin / Sakit.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="mSubmitBtn">Simpan</button>
        </div>
      </form>
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

      // kalau sama dengan query saat ini, jangan reload
      const cur = new URLSearchParams(window.location.search).get('date') || '';
      if (dateStr === cur) return;

      dateSelected.value = dateStr;
      redirectWithDate(dateStr);
    }
  });

  if (dateSelected.value) {
    fp.setDate(dateSelected.value, false);
  }

  // ====== MODAL ======
  const absenModalEl = document.getElementById('absenModal');

  const title = document.getElementById('absenModalTitle');
  const mTanggal = document.getElementById('mTanggal');
  const mRiwayatKelasId = document.getElementById('mRiwayatKelasId');
  const mStudentName = document.getElementById('mStudentName');
  const mStatus = document.getElementById('mStatus');
  const mNote = document.getElementById('mNote');
  const noteWrapper = document.getElementById('noteWrapper');
  const btnSubmit = document.getElementById('mSubmitBtn');

  function toggleNote(status) {
    const need = (status === 'izin' || status === 'sakit');
    if (need) {
      noteWrapper.classList.remove('d-none');
      mNote.required = true;
    } else {
      noteWrapper.classList.add('d-none');
      mNote.required = false;
      mNote.value = '';
    }
  }

  mStatus.addEventListener('change', () => toggleNote(mStatus.value));

  absenModalEl.addEventListener('show.bs.modal', function(event) {
    const btn = event.relatedTarget;
    if (!btn) return;

    const mode = btn.getAttribute('data-mode') || 'create';
    const rkId = btn.getAttribute('data-riwayat-kelas-id') || '';
    const studentName = btn.getAttribute('data-student-name') || '';
    const status = btn.getAttribute('data-status') || '';
    const note = btn.getAttribute('data-note') || '';

    // tanggal selalu ambil dari picker/hidden (paling update)
    const date = dateSelected.value || '';

    mTanggal.value = date;
    mRiwayatKelasId.value = rkId;

    mStudentName.value = studentName;
    mStatus.value = status;
    mNote.value = note;

    toggleNote(status);

    if (mode === 'edit') {
      title.textContent = 'Edit Absensi';
      btnSubmit.textContent = 'Update';
    } else {
      title.textContent = 'Input Absensi';
      btnSubmit.textContent = 'Simpan';
    }
  });
</script>
@endsection
