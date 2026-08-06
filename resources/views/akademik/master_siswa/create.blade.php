@extends('layouts.master')

@section('title', 'Tambah Siswa')

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
    <x-breadcrumb item="Manajemen Siswa" active="Tambah Siswa" subItem="Data Siswa" subLink="{{ route('akademik.master-siswa.index') }}" />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Tambah Siswa</h5>
                    <span class="d-block m-t-5">Buat akun login dan profil siswa</span>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form action="{{ route('akademik.master-siswa.store') }}" method="POST">
                        @csrf

                        <h6 class="mb-3">Akun Login</h6>

                        <div class="mb-3">
                            <label>Nama</label>
                            <input type="text" name="name" value="{{ old('name') }}"
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
                                <input type="text" name="nis" value="{{ old('nis') }}"
                                    class="form-control @error('nis') is-invalid @enderror">
                                @error('nis')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>NISN</label>
                                <input type="text" name="nisn" value="{{ old('nisn') }}"
                                    class="form-control @error('nisn') is-invalid @enderror">
                                @error('nisn')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin"
                                    class="form-control @error('jenis_kelamin') is-invalid @enderror">
                                    <option value="">Pilih</option>
                                    <option value="l" {{ old('jenis_kelamin') == 'l' ? 'selected' : '' }}>Laki-laki
                                    </option>
                                    <option value="p" {{ old('jenis_kelamin') == 'p' ? 'selected' : '' }}>Perempuan
                                    </option>
                                </select>
                                @error('jenis_kelamin')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                                    class="form-control @error('tanggal_lahir') is-invalid @enderror">
                                @error('tanggal_lahir')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Agama</label>
                                <input type="text" name="agama" value="{{ old('agama') }}"
                                    class="form-control @error('agama') is-invalid @enderror">
                                @error('agama')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Tempat Lahir (Kabupaten/Kota)</label>
                                <select name="tempat_lahir_kabupaten_id" id="tempat_lahir_kabupaten_id"
                                    class="form-control @error('tempat_lahir_kabupaten_id') is-invalid @enderror"
                                    data-trigger>
                                    <option value="">Ketik untuk mencari...</option>

                                    {{-- kalau old ada, inject option supaya tetap kepilih setelah submit gagal --}}
                                    @if (old('tempat_lahir_kabupaten_id') && isset($tempatLahirLabel))
                                        <option value="{{ old('tempat_lahir_kabupaten_id') }}" selected>
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
                            <label>Sekolah Asal</label>
                            <input type="text" name="pendidikan_sebelumnya" value="{{ old('pendidikan_sebelumnya') }}"
                                class="form-control @error('pendidikan_sebelumnya') is-invalid @enderror">
                            @error('pendidikan_sebelumnya')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">Orang Tua & Domisili</h6>

                        {{-- ROW: Orang tua 3/4 + tombol 1/4 --}}
                        <div class="row align-items-end">
                            <div class="col-md-9 mb-3">
                                <label>Orang Tua</label>
                                <select name="orang_tua_id" id="orang_tua_id"
                                    class="form-control @error('orang_tua_id') is-invalid @enderror" data-trigger>
                                    <option value="">Ketik untuk mencari...</option>

                                    {{-- kalau old ada, inject option supaya tetap kepilih setelah submit gagal --}}
                                    @if (!empty($selectedOrtu))
                                        <option value="{{ $selectedOrtu->orang_tua_id }}" selected
                                            data-jalan="{{ e($selectedOrtu->jalan) }}"
                                            data-kelurahan-id="{{ $selectedOrtu->kelurahan_id }}"
                                            data-kelurahan-label="{{ e($selectedOrtu->kelurahan->nama ?? '') }}">
                                            {{ $selectedOrtu->nama_ayah }} / {{ $selectedOrtu->nama_ibu }}
                                        </option>
                                    @endif
                                </select>

                                @error('orang_tua_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3 d-grid">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOrtu">
                                    + Orang Tua
                                </button>
                            </div>
                        </div>
                        {{-- Collapse tambah orang tua --}}
                        <div class="collapse" id="collapseOrtu">
                            <div class="card card-body border mt-2">
                                <h6 class="mb-3">Tambah Orang Tua (opsional)</h6>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Nama Ayah</label>
                                        <input type="text" name="ortu[nama_ayah]" class="form-control"
                                            value="{{ old('ortu.nama_ayah') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Nama Ibu</label>
                                        <input type="text" name="ortu[nama_ibu]" class="form-control"
                                            value="{{ old('ortu.nama_ibu') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Pekerjaan Ayah</label>
                                        <input type="text" name="ortu[pekerjaan_ayah]" class="form-control"
                                            value="{{ old('ortu.pekerjaan_ayah') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Pekerjaan Ibu</label>
                                        <input type="text" name="ortu[pekerjaan_ibu]" class="form-control"
                                            value="{{ old('ortu.pekerjaan_ibu') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Jalan</label>
                                    <input type="text" id="ortu_jalan" name="ortu[jalan]" class="form-control"
                                        value="{{ old('ortu.jalan') }}">
                                </div>

                                <div class="mb-3">
                                    <label>Kelurahan Orang Tua</label>

                                    <select name="ortu[kelurahan_id]" id="ortu_kelurahan_id"
                                        class="form-control @error('ortu.kelurahan_id') is-invalid @enderror"
                                        data-trigger>
                                        <option value="">Ketik untuk mencari kelurahan...</option>
                                        @if (old('ortu.kelurahan_id') && isset($ortuKelurahanLabel))
                                            <option value="{{ old('ortu.kelurahan_id') }}" selected>
                                                {{ $ortuKelurahanLabel }}</option>
                                        @endif
                                    </select>

                                    @error('ortu.kelurahan_id')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror

                                    <small class="text-muted">Cari bisa kena kelurahan/kecamatan/kabupaten/provinsi</small>
                                </div>


                                <small class="text-muted d-block">
                                    * Jika orang_tua_id kosong tapi ortu[...] terisi, controller bisa membuat orang tua baru
                                    lalu pakai id-nya.
                                </small>
                            </div>
                        </div>

                        {{-- ROW: Kelurahan domisili di bawah --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label>Kelurahan Domisili Siswa</label>

                                {{-- Select ini dipakai Choices (remote search) --}}
                                <select name="kelurahan_id" id="kelurahan_id"
                                    class="form-control @error('kelurahan_id') is-invalid @enderror" data-trigger>
                                    <option value="">Ketik untuk mencari kelurahan...</option>

                                    {{-- kalau old ada, biar tetap kepilih --}}
                                    @if (old('kelurahan_id') && isset($kelurahanLabel))
                                        <option value="{{ old('kelurahan_id') }}" selected>{{ $kelurahanLabel }}</option>
                                    @endif
                                </select>

                                {{-- hidden ini dipakai saat checkbox dicentang (karena select akan di-disable dan tidak terkirim) --}}
                                <input type="hidden" id="kelurahan_id_hidden" name="kelurahan_id_hidden"
                                    value="">

                                @error('kelurahan_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Cari bisa kena kelurahan/kecamatan/kabupaten/provinsi</small>
                            </div>
                        </div>

                        {{-- Checkbox di bawah --}}
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

                        {{-- Alamat di bawah, otomatis isi kalau sama ortu --}}
                        <div class="row">
                            <div class="col-12 mb-3" id="alamat_wrap">
                                <label>Alamat (Jalan / RT RW / Detail)</label>
                                <textarea name="alamat" id="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat') }}</textarea>
                                @error('alamat')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>



                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-success">Simpan</button>
                            <a href="{{ route('akademik.master-siswa.index') }}"
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

            // ===== Utils =====
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

            async function fetchChoicesResultsWithMeta(urlStr) {
                const res = await fetch(urlStr, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return {
                    items: [],
                    more: false
                };

                const data = await res.json();
                const results = data.results || [];
                const more = !!(data.pagination && data.pagination.more);

                const items = results.map(r => ({
                    value: String(r.id),
                    label: r.text,
                    customProperties: {
                        jalan: r.jalan || '',
                        kelurahanId: r.kelurahan_id || '',
                        kelurahanLabel: r.kelurahan_label || ''
                    }
                }));

                return {
                    items,
                    more
                };
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

            function initRemoteChoicesWithMeta(selectId, routeUrl, {
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

                const metaMap = new Map();

                const doSearch = debounce(async (value) => {
                    const q = (value || '').trim();
                    if (q.length < minInput) return;

                    const url = new URL(routeUrl, window.location.origin);
                    url.searchParams.set('q', q);
                    url.searchParams.set('page', '1');

                    const {
                        items
                    } = await fetchChoicesResultsWithMeta(url.toString());

                    metaMap.clear();
                    items.forEach(it => metaMap.set(String(it.value), it.customProperties || {}));

                    instance.setChoices(items, 'value', 'label', true);
                }, 300);

                el.addEventListener('search', function(event) {
                    doSearch(event.detail.value);
                });

                el.addEventListener('change', function() {
                    const val = String(el.value || '');
                    if (!val) return;

                    const meta = metaMap.get(val);
                    if (!meta) return;

                    const opt = el.querySelector(`option[value="${CSS.escape(val)}"]`);
                    if (!opt) return;

                    opt.dataset.jalan = meta.jalan || '';
                    opt.dataset.kelurahanId = meta.kelurahanId || '';
                    opt.dataset.kelurahanLabel = meta.kelurahanLabel || '';
                });

                return instance;
            }

            function getSelectedOption(selectEl) {
                if (!selectEl) return null;
                return selectEl.options[selectEl.selectedIndex] || null;
            }

            function setKelurahanSiswaValue(kelChoices, kelSelectEl, hiddenEl, kelId, kelLabel) {
                if (!kelId) return;

                // set hidden agar tetap terkirim walau select disabled
                if (hiddenEl) hiddenEl.value = kelId;

                // inject ke UI choices supaya terlihat
                if (kelChoices) {
                    kelChoices.setChoices([{
                        value: kelId,
                        label: kelLabel || 'Kelurahan'
                    }], 'value', 'label', true);
                    kelChoices.setChoiceByValue(kelId);
                    kelChoices.disable();
                }

                if (kelSelectEl) kelSelectEl.disabled = true;
            }

            function enableKelurahanSiswa(kelChoices, kelSelectEl, hiddenEl) {
                if (kelChoices) kelChoices.enable();
                if (kelSelectEl) kelSelectEl.disabled = false;
                if (hiddenEl) hiddenEl.value = '';

                // if (kelSelectEl) {
                //     kelSelectEl.addEventListener('change', function() {
                //         if (hiddenEl) hiddenEl.value = kelSelectEl.value;
                //     });
                // }
            }

            // ===== Init choices remote =====
            const ortuChoices = initRemoteChoicesWithMeta(
                'orang_tua_id',
                "{{ route('ajax.orang_tua') }}", {
                    placeholder: 'Ketik untuk mencari orang tua...',
                    searchPlaceholder: 'Cari nama ayah / ibu...'
                }
            );

            const tempatLahirChoices = initRemoteChoices(
                'tempat_lahir_kabupaten_id',
                "{{ route('ajax.tempat_lahir.kabupaten') }}", {
                    placeholder: 'Ketik untuk mencari...',
                    searchPlaceholder: 'Cari kabupaten / provinsi...'
                }
            );

            const kelSiswaChoices = initRemoteChoices(
                'kelurahan_id',
                "{{ route('ajax.domisili.kelurahan') }}", {
                    placeholder: 'Ketik untuk mencari kelurahan...',
                    searchPlaceholder: 'Cari kelurahan / kecamatan / kabupaten / provinsi...'
                }
            );

            const kelOrtuChoices = initRemoteChoices(
                'ortu_kelurahan_id',
                "{{ route('ajax.domisili.kelurahan') }}", {
                    placeholder: 'Ketik untuk mencari kelurahan...',
                    searchPlaceholder: 'Cari kelurahan / kecamatan / kabupaten / provinsi...'
                }
            );

            // ===== Elements =====
            const ortuSelect = document.getElementById('orang_tua_id');
            const alamatSama = document.getElementById('alamat_sama_ortu');
            const alamat = document.getElementById('alamat');

            const kelSiswaEl = document.getElementById('kelurahan_id');
            const hiddenKelSiswa = document.getElementById('kelurahan_id_hidden');
            if (kelSiswaEl && hiddenKelSiswa) {
                kelSiswaEl.addEventListener('change', function() {
                    hiddenKelSiswa.value = kelSiswaEl.value;
                });
            }

            const collapseEl = document.getElementById('collapseOrtu');

            // field ortu baru
            const ortuJalanEl = document.getElementById('ortu_jalan'); // <-- pastikan ada id ini
            const ortuKelEl = document.getElementById('ortu_kelurahan_id');

            function isCreateOrtuOpen() {
                // bootstrap collapse pakai class "show"
                return !!(collapseEl && collapseEl.classList.contains('show'));
            }

            // Ambil sumber alamat & kelurahan dari:
            // - existing ortu (select)
            // - atau ortu baru (form collapse)
            function getAlamatKelurahanSource() {
                if (isCreateOrtuOpen()) {
                    const jalan = (ortuJalanEl?.value || '').trim();

                    // ambil value dan label dari select ortu kelurahan (Choices)
                    const kelId = (ortuKelEl?.value || '').trim();

                    let kelLabel = '';
                    if (ortuKelEl && ortuKelEl.selectedOptions && ortuKelEl.selectedOptions[0]) {
                        kelLabel = (ortuKelEl.selectedOptions[0].text || '').trim();
                    }

                    return {
                        jalan,
                        kelId,
                        kelLabel,
                        mode: 'create'
                    };
                }

                const opt = getSelectedOption(ortuSelect);
                const jalan = (opt?.dataset?.jalan || '').trim();
                const kelId = (opt?.dataset?.kelurahanId || '').trim();
                const kelLabel = (opt?.dataset?.kelurahanLabel || '').trim();

                return {
                    jalan,
                    kelId,
                    kelLabel,
                    mode: 'existing'
                };
            }

            // ===== Checkbox logic =====
            function applyAlamatOrtu() {
                const same = !!(alamatSama && alamatSama.checked);

                // kalau tidak sama ortu: normal
                if (!same) {
                    enableKelurahanSiswa(kelSiswaChoices, kelSiswaEl, hiddenKelSiswa);
                    if (alamat) alamat.readOnly = false;
                    return;
                }

                // sama ortu: ambil dari source yang benar
                const src = getAlamatKelurahanSource();

                if (alamat) {
                    alamat.value = src.jalan || '';
                    alamat.readOnly = true;
                }

                if (src.kelId) {
                    setKelurahanSiswaValue(kelSiswaChoices, kelSiswaEl, hiddenKelSiswa, src.kelId, src.kelLabel ||
                        'Kelurahan');
                } else {
                    // kalau kelurahan belum dipilih (ortu baru belum pilih kel)
                    if (kelSiswaChoices) kelSiswaChoices.disable();
                    if (kelSiswaEl) kelSiswaEl.disabled = true;
                    if (hiddenKelSiswa) hiddenKelSiswa.value = '';
                }
            }

            // ===== Events =====
            if (alamatSama) {
                alamatSama.addEventListener('change', applyAlamatOrtu);
            }

            if (ortuSelect) {
                ortuSelect.addEventListener('change', function() {
                    if (alamatSama && alamatSama.checked && !isCreateOrtuOpen()) applyAlamatOrtu();
                });
            }

            // kalau user ngisi ortu baru, dan checkbox aktif => update realtime
            if (ortuJalanEl) {
                ortuJalanEl.addEventListener('input', function() {
                    if (alamatSama && alamatSama.checked && isCreateOrtuOpen()) applyAlamatOrtu();
                });
            }
            if (ortuKelEl) {
                ortuKelEl.addEventListener('change', function() {
                    if (alamatSama && alamatSama.checked && isCreateOrtuOpen()) applyAlamatOrtu();
                });
            }

            // ===== Disable select orang tua saat create ortu dibuka (checkbox TETAP bisa dipakai) =====
            if (collapseEl) {
                collapseEl.addEventListener('show.bs.collapse', function() {
                    // disable pilih ortu existing
                    if (ortuSelect) ortuSelect.disabled = true;

                    // checkbox tetap aktif -> jangan disable, jangan auto-uncheck
                    // tapi kita re-apply supaya sumber data pindah ke "ortu baru"
                    applyAlamatOrtu();
                });

                collapseEl.addEventListener('hidden.bs.collapse', function() {
                    if (ortuSelect) ortuSelect.disabled = false;

                    // balik ke mode existing -> re-apply supaya sumber data balik ke select
                    applyAlamatOrtu();
                });
            }

            // initial run
            applyAlamatOrtu();
        });
    </script>
@endsection
