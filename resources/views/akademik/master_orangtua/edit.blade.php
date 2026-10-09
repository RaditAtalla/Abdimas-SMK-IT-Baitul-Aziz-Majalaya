@extends('layouts.master')

@section('title', 'Edit Data Orang Tua')

@section('css')
    <link rel="stylesheet" href="/build/css/plugins/style.css" />
    <style>
        body[data-pc-theme="dark"] .choices__inner {
            background-color: rgba(255, 255, 255, .06) !important;
            border-color: rgba(255, 255, 255, .18) !important;
            color: rgba(255, 255, 255, .90) !important;
        }
        body[data-pc-theme="dark"] .choices__input,
        body[data-pc-theme="dark"] .choices__input::placeholder,
        body[data-pc-theme="dark"] .choices__list--dropdown,
        body[data-pc-theme="dark"] .choices__list[aria-expanded],
        body[data-pc-theme="dark"] .choices__item,
        body[data-pc-theme="dark"] .choices__item--selectable {
            color: rgba(255, 255, 255, .92) !important;
        }
        body[data-pc-theme="dark"] .choices__list--dropdown {
            background-color: #1b1f24 !important;
            border-color: rgba(255, 255, 255, .14) !important;
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb item="Manajemen Akun" active="Edit Orang Tua" subItem="Data Orang Tua" subLink="{{ route('akademik.master-orang-tua.index') }}" />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Orang Tua</h5>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form action="{{ route('akademik.master-orang-tua.update', $orangTua->orang_tua_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h6 class="mb-3">Akun Login (Opsional, kosongkan jika tidak ingin mengubah password)</h6>
                        <div class="mb-3">
                            <label>Password Baru</label>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">Profil Orang Tua</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nama Ayah</label>
                                <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $orangTua->nama_ayah) }}"
                                    class="form-control @error('nama_ayah') is-invalid @enderror">
                                @error('nama_ayah')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Nama Ibu</label>
                                <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $orangTua->nama_ibu) }}"
                                    class="form-control @error('nama_ibu') is-invalid @enderror">
                                @error('nama_ibu')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Pekerjaan Ayah</label>
                                <input type="text" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah', $orangTua->pekerjaan_ayah) }}"
                                    class="form-control @error('pekerjaan_ayah') is-invalid @enderror">
                                @error('pekerjaan_ayah')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Pekerjaan Ibu</label>
                                <input type="text" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu', $orangTua->pekerjaan_ibu) }}"
                                    class="form-control @error('pekerjaan_ibu') is-invalid @enderror">
                                @error('pekerjaan_ibu')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Jalan</label>
                            <input type="text" name="jalan" class="form-control @error('jalan') is-invalid @enderror"
                                value="{{ old('jalan', $orangTua->jalan) }}">
                            @error('jalan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Kelurahan Domisili</label>
                            <select name="kelurahan_id" id="kelurahan_id"
                                class="form-control @error('kelurahan_id') is-invalid @enderror" data-trigger>
                                <option value="">Ketik untuk mencari kelurahan...</option>
                                @php
                                    $valKelId = old('kelurahan_id', $orangTua->kelurahan_id);
                                @endphp
                                @if ($valKelId && isset($ortuKelurahanLabel))
                                    <option value="{{ $valKelId }}" selected>{{ $ortuKelurahanLabel }}</option>
                                @endif
                            </select>
                            @error('kelurahan_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Cari bisa kena kelurahan/kecamatan/kabupaten/provinsi</small>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-success">Update</button>
                            <a href="{{ route('akademik.master-orang-tua.index') }}"
                                class="btn btn-light">Batal</a>
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
                const res = await fetch(urlStr, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return [];
                const data = await res.json();
                return (data.results || []).map(r => ({ value: r.id, label: r.text }));
            }

            const kelEl = document.getElementById('kelurahan_id');
            if (kelEl) {
                const instance = new Choices(kelEl, {
                    searchEnabled: true,
                    placeholder: true,
                    placeholderValue: 'Ketik untuk mencari kelurahan...',
                    searchPlaceholderValue: 'Cari kelurahan / kecamatan / kabupaten / provinsi...',
                    shouldSort: false,
                    itemSelectText: '',
                    searchResultLimit: 15,
                    renderChoiceLimit: 15
                });

                const doSearch = debounce(async (value) => {
                    const q = (value || '').trim();
                    if (q.length < 2) return;

                    const url = new URL("{{ route('ajax.domisili.kelurahan') }}", window.location.origin);
                    url.searchParams.set('q', q);
                    url.searchParams.set('page', '1');

                    const items = await fetchSelect2Results(url.toString());
                    instance.setChoices(items, 'value', 'label', true);
                }, 300);

                kelEl.addEventListener('search', function(event) {
                    doSearch(event.detail.value);
                });
            }
        });
    </script>
@endsection
