@extends('layouts.master')

@section('title', 'Lingkup Materi')

@section('css')
<link rel="stylesheet" href="/build/css/plugins/style.css" />
@endsection



@section('content')

<x-breadcrumb item="Intrakurikuler" subItem="Intrakurikuler" subLink="{{ route('intrakurikuler.index') }}" active="Lingkup Materi" />

<div class="row">
  <div class="col-xl-12">
    <div class="card">

      {{-- Header --}}
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h5 class="mb-0">{{ $intrakurikuler->nama_pelajaran }}</h5>
          <span class="d-block m-t-5">
            {{ $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '-' }}
            •
            {{ $intrakurikuler->kelasAjar?->tahunAjaran?->tahun ?? '-' }}
            {{ $intrakurikuler->kelasAjar?->tahunAjaran?->semester ?? '' }}
          </span>
        </div>

        @role('Bagian Akademik|Super Admin|Guru Mapel')
        <button
          type="button"
          class="btn btn-primary"
          data-bs-toggle="modal"
          data-bs-target="#lingkupMateriModal"
          data-mode="create"
          data-title="Tambah Lingkup Materi"
          data-id=""
          data-nama="">
          Tambah Lingkup Materi
        </button>
        @endrole
      </div>

      {{-- Body --}}
      <div class="card-body table-border-style">
        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <div class="table-responsive">
          <table class="table" id="pc-dt-simple">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama materi</th>
                <th style="width: 180px">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($lingkupMateri as $lm)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $lm->nama_materi }}</td>
                <td>
  @role('Bagian Akademik|Super Admin|Guru Mapel')
    <div class="d-inline-flex align-items-center gap-2">
      <button
        type="button"
        class="btn btn-sm btn-light-warning"
        data-bs-toggle="modal"
        data-bs-target="#lingkupMateriModal"
        data-mode="edit"
        data-title="Edit Lingkup Materi"
        data-id="{{ $lm->lingkup_materi_id }}"
        data-nama="{{ e($lm->nama_materi) }}">
        Update
      </button>

      <button
        type="button"
        class="btn btn-sm btn-light-danger text-lg"
        data-bs-toggle="modal"
        data-bs-target="#confirmDeleteModal"
        data-action="{{ route('lingkup-materi.destroy', ['intrakurikuler' => $intrakurikuler->intrakurikuler_id, 'lingkup_materi' => $lm->lingkup_materi_id]) }}"
        data-name="{{ e($lm->nama_materi) }}">
        Delete
      </button>
    </div>
  @endrole
</td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center">Belum ada lingkup materi.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <a href="{{ route('intrakurikuler.index') }}" class="btn btn-light-secondary">Kembali</a>
      </div>
    </div>
  </div>
</div>

{{-- ===================== MODAL ADD/EDIT ===================== --}}
<div
  class="modal fade"
  id="lingkupMateriModal"
  tabindex="-1"
  aria-hidden="true"

  {{-- lempar data dari PHP ke HTML, supaya JS murni --}}
  data-store-url="{{ route('lingkup-materi.store', $intrakurikuler->intrakurikuler_id) }}"
  data-update-url-template="{{ route('lingkup-materi.update', [$intrakurikuler->intrakurikuler_id, '___ID___']) }}"
  data-has-errors="{{ $errors->any() ? 1 : 0 }}">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <form id="lingkupMateriForm"
        method="POST"
        action="{{ route('lingkup-materi.store', $intrakurikuler->intrakurikuler_id) }}">
        @csrf

        {{-- Spoof method: JS cukup ganti value input ini --}}
        <input type="hidden" name="_method" id="lm_method" value="POST">

        <div class="modal-header">
          <h5 class="modal-title" id="lingkupMateriModalTitle">Tambah Lingkup Materi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" id="lm_id">

          <input type="hidden" name="intrakurikuler_id" value="{{ $intrakurikuler->intrakurikuler_id }}">

          <div class="mb-3">
            <label class="form-label">Nama materi</label>
            <input type="text"
              class="form-control @error('nama_materi') is-invalid @enderror"
              name="nama_materi"
              id="lm_nama"
              placeholder="Masukkan nama materi"
              value="{{ old('nama_materi') }}"
              required>

            @error('nama_materi')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- modal delete -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p class="mb-0">Yakin hapus <strong id="deleteItemName">data</strong>?</p>
      </div>

      <div class="modal-footer d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>

        <form id="deleteConfirmForm" method="POST" class="m-0">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Ya, Hapus</button>
        </form>
      </div>

    </div>
  </div>
</div>


@endsection

@section('scripts')
<!-- [Page Specific JS] start -->
<script type="module">
  import {
    DataTable
  } from '/build/js/plugins/module.js';
  window.dt = new DataTable('#pc-dt-simple');
</script>

<script>
  (function() {
    const modalEl = document.getElementById('lingkupMateriModal');
    if (!modalEl) return;

    // ambil data dari dataset (hasil render Blade) => JS tetap pure
    const storeUrl = modalEl.dataset.storeUrl;
    const updateUrlTemplate = modalEl.dataset.updateUrlTemplate;

    const form = document.getElementById('lingkupMateriForm');
    const methodInput = document.getElementById('lm_method');

    const titleEl = document.getElementById('lingkupMateriModalTitle');
    const idEl = document.getElementById('lm_id');
    const namaEl = document.getElementById('lm_nama');

    modalEl.addEventListener('show.bs.modal', function(event) {
      const btn = event.relatedTarget;
      if (!btn) return;

      const mode = btn.getAttribute('data-mode') || 'create';
      const title = btn.getAttribute('data-title') || 'Tambah Lingkup Materi';
      const id = btn.getAttribute('data-id') || '';
      const nama = btn.getAttribute('data-nama') || '';

      titleEl.textContent = title;
      idEl.value = id;
      namaEl.value = nama;

      if (mode === 'edit' && id) {
        form.action = updateUrlTemplate.replace('___ID___', id);
        methodInput.value = 'PUT';
      } else {
        form.action = storeUrl;
        methodInput.value = 'POST';
      }
    });

    modalEl.addEventListener('hidden.bs.modal', function() {
      idEl.value = '';
      namaEl.value = '';
      methodInput.value = 'POST';
      form.action = storeUrl;
      titleEl.textContent = 'Tambah Lingkup Materi';
    });

    // kalau validasi error, buka modal otomatis (tanpa Blade di JS)
    if (modalEl.dataset.hasErrors === "1") {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  })();

  (function() {
    const modalEl = document.getElementById('confirmDeleteModal');
    if (!modalEl) return;

    const nameEl = document.getElementById('deleteItemName');
    const formEl = document.getElementById('deleteConfirmForm');

    modalEl.addEventListener('show.bs.modal', function(event) {
      const btn = event.relatedTarget;
      if (!btn) return;

      formEl.action = btn.getAttribute('data-action');
      nameEl.textContent = btn.getAttribute('data-name') || 'data ini';
    });
  })();
</script>
<!-- [Page Specific JS] end -->
@endsection