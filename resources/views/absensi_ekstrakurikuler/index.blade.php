@extends('layouts.master')

@section('title', 'Absen Harian (Ekstrakurikuler)')

@section('css')
<link rel="stylesheet" href="/build/css/plugins/style.css" />
<link rel="stylesheet" href="/build/css/plugins/flatpickr.min.css" />
@endsection

@section('content')
<x-breadcrumb item="Absensi Ekstrakurikuler" link="{{ route('absensi.ekstrakurikuler.list') }}" active="Absen Harian" />

<div class="row">
  <div class="col-12">
    <div class="card table-card">
      <div class="card-header">
        <div class="d-sm-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-1">Absen Harian - {{ $ekstrakurikuler->nama_pelajaran }}</h5>
            <small class="text-muted">
              {{ $ekstrakurikuler->tahunAjaran?->tahun ?? '-' }} {{ $ekstrakurikuler->tahunAjaran?->semester ?? '' }} •
              Pembina: {{ $ekstrakurikuler->pembina?->name ?? '-' }}
            </small>
          </div>

          <div class="d-flex gap-2">
            <a href="{{ route('absensi.ekstrakurikuler.rekap', $ekstrakurikuler->ekstrakurikuler_id) }}"
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

          <div class="col-12 col-md-4">
            <button type="button" id="btnApply" class="btn btn-primary">Terapkan</button>
          </div>
        </div>
      </div>

      @if ($errors->any())
        <div class="alert alert-danger m-3">
          <ul class="mb-0">
            @foreach ($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="card-body pt-3">
        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('warning')) <div class="alert alert-warning">{{ session('warning') }}</div> @endif

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
                  $att = $attendanceMap->get($s['siswa_ekstrakurikuler_id']);
                @endphp

                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <img src="{{ $s['avatar'] }}" alt="user" class="img-radius wid-40" />
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ $s['name'] }}</h6>
                        <small class="text-muted">{{ $ekstrakurikuler->nama_pelajaran }}</small>
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
                      class="btn {{ $att ? 'btn-outline-primary' : 'btn-primary' }} btn-sm btnOpenModal"
                      data-bs-toggle="modal"
                      data-bs-target="#absenModal"
                      data-date="{{ $selectedDate }}"
                      data-siswa-ekstrakurikuler-id="{{ $s['siswa_ekstrakurikuler_id'] }}"
                      data-student-name="{{ $s['name'] }}"
                      data-status="{{ $att?->status ?? '' }}"
                      data-note="{{ $att?->note ?? '' }}"
                      data-mode="{{ $att ? 'edit' : 'create' }}"
                    >
                      {{ $att ? 'Edit' : 'Absensi' }}
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>

          </table>
        </div>
                </div>
        <div class="ps-3 mb-3">
          <a href="{{ route('absensi.ekstrakurikuler.list') }}" class="btn btn-light-secondary px-3">
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
      <form method="POST" action="{{ route('absensi.ekstrakurikuler.harian.store', $ekstrakurikuler->ekstrakurikuler_id) }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title" id="absenModalTitle">Absensi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          {{-- ✅ kasih default value biar nggak mungkin kosong --}}
          <input type="hidden" name="tanggal" id="mTanggal" value="{{ $selectedDate }}">
          <input type="hidden" name="siswa_ekstrakurikuler_id" id="mSiswaEkstraId" value="">

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
  // date picker
  const pickDate = document.getElementById('pickDate');
  const dateSelected = document.getElementById('dateSelected');
  const btnApply = document.getElementById('btnApply');

  const fp = flatpickr(pickDate, {
    mode: 'single',
    dateFormat: 'Y-m-d',
    defaultDate: dateSelected.value || null,
    maxDate: 'today',
    onChange: function(selectedDates) {
      if (selectedDates.length) {
        dateSelected.value = fp.formatDate(selectedDates[0], 'Y-m-d');
      }
    }
  });

  btnApply.addEventListener('click', () => {
    const params = new URLSearchParams(window.location.search);
    if (dateSelected.value) params.set('date', dateSelected.value); else params.delete('date');
    window.location.href = `${window.location.pathname}?${params.toString()}`;
  });

  // modal refs
  const title = document.getElementById('absenModalTitle');
  const mTanggal = document.getElementById('mTanggal');
  const mSiswaEkstraId = document.getElementById('mSiswaEkstraId');
  const mStudentName = document.getElementById('mStudentName');
  const mStatus = document.getElementById('mStatus');
  const mNote = document.getElementById('mNote');
  const noteWrapper = document.getElementById('noteWrapper');
  const btnSubmit = document.getElementById('mSubmitBtn');

  function toggleNote(status) {
    const need = (status === 'izin' || status === 'sakit');
    if (need) { noteWrapper.classList.remove('d-none'); mNote.required = true; }
    else { noteWrapper.classList.add('d-none'); mNote.required = false; mNote.value = ''; }
  }

  mStatus.addEventListener('change', () => toggleNote(mStatus.value));

  // ✅ EVENT DELEGATION: aman walau DataTable redraw
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btnOpenModal');
    if (!btn) return;

    const mode = btn.dataset.mode || 'create';

    // data-siswa-ekstrakurikuler-id => dataset.siswaEkstrakurikulerId
    mTanggal.value = btn.dataset.date || dateSelected.value || '';
    mSiswaEkstraId.value = btn.dataset.siswaEkstrakurikulerId || '';

    mStudentName.value = btn.dataset.studentName || '';
    mStatus.value = btn.dataset.status || '';
    mNote.value = btn.dataset.note || '';

    toggleNote(mStatus.value);

    if (mode === 'edit') { title.textContent = 'Edit Absensi'; btnSubmit.textContent = 'Update'; }
    else { title.textContent = 'Input Absensi'; btnSubmit.textContent = 'Simpan'; }
  });

  // optional: reset modal saat ditutup biar bersih
  const modalEl = document.getElementById('absenModal');
  modalEl.addEventListener('hidden.bs.modal', () => {
    mStatus.value = '';
    mNote.value = '';
    toggleNote('');
  });
</script>
@endsection
