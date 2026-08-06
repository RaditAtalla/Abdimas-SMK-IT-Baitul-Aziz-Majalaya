@extends('layouts.master')

@section('title', 'Edit Profil Sekolah')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
    <style>
        body[data-pc-theme="dark"] .choices__inner {
            background-color: rgba(255, 255, 255, .06) !important;
            border-color: rgba(255, 255, 255, .18) !important;
            color: rgba(255, 255, 255, .90) !important;
        }

        body[data-pc-theme="dark"] .choices__input {
            background-color: transparent !important;
            color: rgba(255, 255, 255, .92) !important;
        }

        body[data-pc-theme="dark"] .choices__input::placeholder {
            color: rgba(255, 255, 255, .55) !important;
        }

        body[data-pc-theme="dark"] .choices__list--dropdown,
        body[data-pc-theme="dark"] .choices__list[aria-expanded] {
            background-color: #1b1f24 !important;
            border-color: rgba(255, 255, 255, .14) !important;
            color: rgba(255, 255, 255, .92) !important;
        }

        body[data-pc-theme="dark"] .choices__list--dropdown .choices__item {
            color: rgba(255, 255, 255, .92) !important;
        }

        body[data-pc-theme="dark"] .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: rgba(255, 255, 255, .08) !important;
        }

        body[data-pc-theme="dark"] .choices__item--selectable,
        body[data-pc-theme="dark"] .choices__list--single .choices__item {
            color: rgba(255, 255, 255, .92) !important;
        }

        body[data-pc-theme="dark"] select.is-invalid+.choices .choices__inner {
            border-color: #dc3545 !important;
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb item="Sekolah" link="{{ route('akademik.sekolah.index') }}" active="Edit Profil Sekolah" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card">

                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Edit Data Sekolah</h5>
                        <span class="d-block m-t-5">Perbarui informasi profil sekolah</span>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('akademik.sekolah.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NPSN</label>
                                <input type="text" class="form-control" value="{{ $sekolah->npsn }}" disabled>
                                <small class="text-muted">NPSN tidak bisa diubah.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Sekolah</label>
                                <input type="text" name="nama_sekolah"
                                    class="form-control @error('nama_sekolah') is-invalid @enderror"
                                    value="{{ old('nama_sekolah', $sekolah->nama_sekolah) }}" required>
                                @error('nama_sekolah')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">NSS</label>
                                <input type="text" name="nss" class="form-control @error('nss') is-invalid @enderror"
                                    value="{{ old('nss', $sekolah->nss) }}">
                                @error('nss')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" name="kode_pos"
                                    class="form-control @error('kode_pos') is-invalid @enderror"
                                    value="{{ old('kode_pos', $sekolah->kode_pos) }}">
                                @error('kode_pos')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $sekolah->alamat) }}</textarea>
                                @error('alamat')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kelurahan</label>

                                <select name="kelurahan_id" id="kelurahan_id"
                                    class="form-control @error('kelurahan_id') is-invalid @enderror" data-trigger>
                                    <option value="">Ketik untuk mencari kelurahan...</option>

                                    @php
                                        $selectedKelId = old('kelurahan_id', $sekolah->kelurahan_id);
                                    @endphp

                                    @if ($selectedKelId && $kelurahanLabel)
                                        <option value="{{ $selectedKelId }}" selected>{{ $kelurahanLabel }}</option>
                                    @endif
                                </select>

                                @error('kelurahan_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror

                                <small class="text-muted">Cari bisa kena kelurahan/kecamatan/kabupaten/provinsi</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="text" name="website"
                                    class="form-control @error('website') is-invalid @enderror"
                                    value="{{ old('website', $sekolah->website) }}">
                                @error('website')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $sekolah->email) }}">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="telp"
                                    class="form-control @error('telp') is-invalid @enderror"
                                    value="{{ old('telp', $sekolah->telp) }}">
                                @error('telp')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Kepala Sekolah sekarang diambil dari relasi staff --}}
                            <div class="col-md-6">
                                <label class="form-label">Nama Kepala Sekolah</label>
                                <input type="text" name="nama_kepala_sekolah"
                                    class="form-control @error('nama_kepala_sekolah') is-invalid @enderror"
                                    value="{{ old('nama_kepala_sekolah', $sekolah->staff?->nama) }}">
                                @error('nama_kepala_sekolah')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">NUPTK Kepala Sekolah</label>
                                <input type="text" name="nuptk_kepala_sekolah"
                                    class="form-control @error('nuptk_kepala_sekolah') is-invalid @enderror"
                                    value="{{ old('nuptk_kepala_sekolah', $sekolah->staff?->nuptk) }}">
                                @error('nuptk_kepala_sekolah')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('akademik.sekolah.index') }}" class="btn btn-light-secondary">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/build/js/plugins/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function debounce(fn, ms) {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...args), ms);
                };
            }

            async function fetchSelect2Results(urlStr) {
                const res = await fetch(urlStr, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return [];
                const data = await res.json();
                return (data.results || []).map(r => ({
                    value: r.id,
                    label: r.text
                }));
            }

            function initRemoteChoices(selectId, routeUrl, {
                minInput = 2
            } = {}) {
                const el = document.getElementById(selectId);
                if (!el) return;

                const instance = new Choices(el, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: '',
                    searchResultLimit: 15,
                    renderChoiceLimit: 15,
                });

                const doSearch = debounce(async (value) => {
                    const q = (value || '').trim();
                    if (q.length < minInput) return;

                    const url = new URL(routeUrl, window.location.origin);
                    url.searchParams.set('q', q);
                    url.searchParams.set('page', '1');

                    const items = await fetchSelect2Results(url.toString());
                    instance.setChoices(items, 'value', 'label', true);
                }, 300);

                el.addEventListener('search', function(event) {
                    doSearch(event.detail.value);
                });
            }

            setTimeout(() => {
                initRemoteChoices('kelurahan_id', "{{ route('ajax.domisili.kelurahan') }}");
            }, 50);
        });
    </script>
@endsection