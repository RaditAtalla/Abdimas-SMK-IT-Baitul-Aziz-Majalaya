@extends('layouts.master')

@section('title', 'Edit Siswa')

@section('css')
<link rel="stylesheet" href="/build/css/plugins/style.css" />
<style>
    /* ===== Choices DARK MODE FIX (Able Pro uses body[data-pc-theme="dark"]) ===== */
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

    body[data-pc-theme="dark"] .choices__item--selectable {
        color: rgba(255, 255, 255, .92) !important;
    }

    /* selected item chip (kalau single select, ini text yang tampil) */
    body[data-pc-theme="dark"] .choices__item--selectable,
    body[data-pc-theme="dark"] .choices__list--single .choices__item {
        color: rgba(255, 255, 255, .92) !important;
    }

    /* kalau invalid, tetap merah */
    body[data-pc-theme="dark"] select.is-invalid+.choices .choices__inner {
        border-color: #dc3545 !important;
    }
</style>
@endsection

@section('content')
<x-breadcrumb item="Manajemen Siswa" active="Edit Siswa" subItem="Data Siswa" subLink="{{ route('akademik.master-siswa.index') }}" />

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Edit Siswa</h5>
                <span class="d-block m-t-5">Edit akun login dan profil siswa</span>
            </div>

            <div class="card-body">
                <form action="{{ route('akademik.master-siswa.update', $siswa->siswa_id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="mb-3">Akun Login</h6>

                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="name" value="{{ old('name', $siswa->user->name) }}"
                            class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Profil Siswa</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>NIS</label>
                            <input type="text" name="nis" value="{{ old('nis', $siswa->nis) }}"
                                class="form-control @error('nis') is-invalid @enderror">
                            @error('nis')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>NISN</label>
                            <input type="text" name="nisn" value="{{ old('nisn', $siswa->nisn) }}"
                                class="form-control @error('nisn') is-invalid @enderror">
                            @error('nisn')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror">
                                <option value="">Pilih</option>
                                <option value="l" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'l' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="p" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'p' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                value="{{ old('tanggal_lahir', optional($siswa->tanggal_lahir)->format('Y-m-d')) }}"
                                class="form-control @error('tanggal_lahir') is-invalid @enderror">
                            @error('tanggal_lahir')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Agama</label>
                            <input type="text" name="agama" value="{{ old('agama', $siswa->agama) }}"
                                class="form-control @error('agama') is-invalid @enderror">
                            @error('agama')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Tempat Lahir (Kabupaten/Kota)</label>
                            <select
                                name="tempat_lahir_kabupaten_id"
                                id="tempat_lahir_kabupaten_id"
                                class="form-control @error('tempat_lahir_kabupaten_id') is-invalid @enderror"
                                data-trigger>
                                <option value="">Ketik untuk mencari...</option>

                                {{-- kalau old ada, inject option supaya tetap kepilih setelah submit gagal --}}
                                @if(old('tempat_lahir_kabupaten_id', $siswa->tempat_lahir_kabupaten_id) && isset($tempatLahirLabel))
                                <option value="{{ old('tempat_lahir_kabupaten_id', $siswa->tempat_lahir_kabupaten_id) }}" selected>
                                    {{ $tempatLahirLabel }}
                                </option>
                                @endif
                            </select>

                            @error('tempat_lahir_kabupaten_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Cari pakai nama kabupaten atau provinsi</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Pendidikan Sebelumnya</label>
                        <input type="text" name="pendidikan_sebelumnya" value="{{ old('pendidikan_sebelumnya', $siswa->pendidikan_sebelumnya) }}"
                            class="form-control @error('pendidikan_sebelumnya') is-invalid @enderror">
                        @error('pendidikan_sebelumnya')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Orang Tua & Domisili</h6>

                    {{-- DATA ORANG TUA (langsung edit) --}}
                    <div class="card card-body border mb-3">
                        <h6 class="mb-3">Data Orang Tua</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nama Ayah</label>
                                <input type="text" id="ortu_nama_ayah" name="ortu[nama_ayah]"
                                    class="form-control @error('ortu.nama_ayah') is-invalid @enderror"
                                    value="{{ old('ortu.nama_ayah', $siswa->orangTua?->nama_ayah) }}">
                                @error('ortu.nama_ayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Nama Ibu</label>
                                <input type="text" id="ortu_nama_ibu" name="ortu[nama_ibu]"
                                    class="form-control @error('ortu.nama_ibu') is-invalid @enderror"
                                    value="{{ old('ortu.nama_ibu', $siswa->orangTua?->nama_ibu) }}">
                                @error('ortu.nama_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Pekerjaan Ayah</label>
                                <input type="text" id="ortu_pekerjaan_ayah" name="ortu[pekerjaan_ayah]"
                                    class="form-control @error('ortu.pekerjaan_ayah') is-invalid @enderror"
                                    value="{{ old('ortu.pekerjaan_ayah', $siswa->orangTua?->pekerjaan_ayah) }}">
                                @error('ortu.pekerjaan_ayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Pekerjaan Ibu</label>
                                <input type="text" id="ortu_pekerjaan_ibu" name="ortu[pekerjaan_ibu]"
                                    class="form-control @error('ortu.pekerjaan_ibu') is-invalid @enderror"
                                    value="{{ old('ortu.pekerjaan_ibu', $siswa->orangTua?->pekerjaan_ibu) }}">
                                @error('ortu.pekerjaan_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Jalan</label>
                            <input type="text" id="ortu_jalan" name="ortu[jalan]"
                                class="form-control @error('ortu.jalan') is-invalid @enderror"
                                value="{{ old('ortu.jalan', $siswa->orangTua?->jalan) }}">
                            @error('ortu.jalan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label>Kelurahan Orang Tua</label>
                            <select name="ortu[kelurahan_id]" id="ortu_kelurahan_id"
                                class="form-control @error('ortu.kelurahan_id') is-invalid @enderror"
                                data-trigger>
                                <option value="">Ketik untuk mencari kelurahan...</option>

                                @php
                                $ortuKelOld = old('ortu.kelurahan_id', $siswa->orangTua?->kelurahan_id);
                                @endphp

                                @if($ortuKelOld && isset($ortuKelurahanLabel))
                                <option value="{{ $ortuKelOld }}" selected>{{ $ortuKelurahanLabel }}</option>
                                @endif
                            </select>

                            @error('ortu.kelurahan_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror

                            <small class="text-muted">Cari bisa kena kelurahan/kecamatan/kabupaten/provinsi</small>
                        </div>
                    </div>

                    {{-- DOMISILI SISWA --}}
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label>Kelurahan Domisili Siswa</label>

                            <select name="kelurahan_id" id="kelurahan_id"
                                class="form-control @error('kelurahan_id') is-invalid @enderror"
                                data-trigger>
                                <option value="">Ketik untuk mencari kelurahan...</option>

                                @php
                                $kelOld = old('kelurahan_id', $siswa->kelurahan_id);
                                @endphp

                                @if($kelOld && isset($kelurahanLabel))
                                <option value="{{ $kelOld }}" selected>{{ $kelurahanLabel }}</option>
                                @endif
                            </select>

                            <input type="hidden" id="kelurahan_id_hidden" name="kelurahan_id_hidden"
                                value="{{ old('kelurahan_id_hidden', $siswa->kelurahan_id) }}">

                            @error('kelurahan_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="alamat_sama_ortu"
                                    name="alamat_sama_ortu" value="1"
                                    {{ old('alamat_sama_ortu') ? 'checked' : '' }}>
                                <label class="form-check-label" for="alamat_sama_ortu">
                                    Alamat siswa sama dengan orang tua
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3" id="alamat_wrap">
                            <label>Alamat (Jalan / RT RW / Detail)</label>
                            <textarea name="alamat" id="alamat" rows="3"
                                class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $siswa->alamat) }}</textarea>
                            @error('alamat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-success">Simpan</button>
                        <a href="{{ route('akademik.master-siswa.index') }}" class="btn btn-light">Batal</a>
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
            placeholder = 'Ketik untuk mencari...',
            searchPlaceholder = 'Cari...',
            minInput = 2
        } = {}) {
            const el = document.getElementById(selectId);
            if (!el) return null;

            const instance = new Choices(el, {
                searchEnabled: true,
                placeholder: true,
                placeholderValue: placeholder,
                searchPlaceholderValue: searchPlaceholder,
                shouldSort: false,
                itemSelectText: '',
                searchResultLimit: 15,
                renderChoiceLimit: 15
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

            return instance;
        }

        // init remote select
        initRemoteChoices('tempat_lahir_kabupaten_id', "{{ route('ajax.tempat_lahir.kabupaten') }}", {
            placeholder: 'Ketik untuk mencari...',
            searchPlaceholder: 'Cari kabupaten / provinsi...'
        });

        const kelSiswaChoices = initRemoteChoices('kelurahan_id', "{{ route('ajax.domisili.kelurahan') }}", {
            placeholder: 'Ketik untuk mencari kelurahan...',
            searchPlaceholder: 'Cari kelurahan / kecamatan / kabupaten / provinsi...'
        });

        const kelOrtuChoices = initRemoteChoices('ortu_kelurahan_id', "{{ route('ajax.domisili.kelurahan') }}", {
            placeholder: 'Ketik untuk mencari kelurahan...',
            searchPlaceholder: 'Cari kelurahan / kecamatan / kabupaten / provinsi...'
        });

        // elements
        const alamatSama = document.getElementById('alamat_sama_ortu');
        const alamat = document.getElementById('alamat');

        const ortuJalanEl = document.getElementById('ortu_jalan');
        const ortuKelEl = document.getElementById('ortu_kelurahan_id');

        const kelSiswaEl = document.getElementById('kelurahan_id');
        const hiddenKelSiswa = document.getElementById('kelurahan_id_hidden');

        function setKelurahanSiswaLocked(kelId, kelLabel) {
            if (hiddenKelSiswa) hiddenKelSiswa.value = kelId || '';

            if (!kelId) {
                if (kelSiswaChoices) kelSiswaChoices.disable();
                if (kelSiswaEl) kelSiswaEl.disabled = true;
                return;
            }

            if (kelSiswaChoices) {
                kelSiswaChoices.setChoices([{
                    value: kelId,
                    label: kelLabel || 'Kelurahan'
                }], 'value', 'label', true);
                kelSiswaChoices.setChoiceByValue(kelId);
                kelSiswaChoices.disable();
            }
            if (kelSiswaEl) kelSiswaEl.disabled = true;
        }

        function setKelurahanSiswaUnlocked() {
            if (kelSiswaChoices) kelSiswaChoices.enable();
            if (kelSiswaEl) kelSiswaEl.disabled = false;

            // kalau user pilih manual, hidden ngikut value select
            if (hiddenKelSiswa) hiddenKelSiswa.value = kelSiswaEl?.value || '';
        }

        function applyAlamatOrtu() {
            const same = !!(alamatSama && alamatSama.checked);

            if (!same) {
                if (alamat) alamat.readOnly = false;
                setKelurahanSiswaUnlocked();
                return;
            }

            // lock ke data ortu
            const jalan = (ortuJalanEl?.value || '').trim();
            if (alamat) {
                alamat.value = jalan;
                alamat.readOnly = true;
            }

            const kelId = (ortuKelEl?.value || '').trim();
            let kelLabel = '';
            if (ortuKelEl?.selectedOptions?.[0]) {
                kelLabel = (ortuKelEl.selectedOptions[0].text || '').trim();
            }

            setKelurahanSiswaLocked(kelId, kelLabel);
        }

        // sync hidden kalau user pilih manual
        if (kelSiswaEl) {
            kelSiswaEl.addEventListener('change', function() {
                if (!alamatSama?.checked && hiddenKelSiswa) hiddenKelSiswa.value = kelSiswaEl.value || '';
            });
        }

        // realtime update kalau checkbox aktif
        if (ortuJalanEl) ortuJalanEl.addEventListener('input', () => {
            if (alamatSama?.checked) applyAlamatOrtu();
        });
        if (ortuKelEl) ortuKelEl.addEventListener('change', () => {
            if (alamatSama?.checked) applyAlamatOrtu();
        });

        if (alamatSama) alamatSama.addEventListener('change', applyAlamatOrtu);

        // initial
        applyAlamatOrtu();
    });
</script>
@endsection