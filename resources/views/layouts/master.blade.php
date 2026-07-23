<!doctype html>
<html lang="en">
<!-- [Head] start -->
<head>
    <title>@yield('title') | Able Pro Dashboard Template</title>
    @include('layouts/head-page-meta')

    @yield('css')

    @include('layouts/head-css')
</head>
<!-- [Head] end -->

@php
    // Theme terakhir dari cookie (dark / light). Default light.
    $pcTheme = request()->cookie('pc-theme', 'light');
    $pcTheme = in_array($pcTheme, ['dark', 'light']) ? $pcTheme : 'light';
@endphp

<!-- [Body] Start -->
<body
    data-pc-preset="{{ config('app.preset_theme') }}"
    data-pc-sidebar-caption="{{ config('app.caption_show') }}"
    data-pc-layout="{{ config('app.theme_layout') }}"
    data-pc-direction="{{ config('app.rtlflag') }}"
    data-pc-theme="{{ $pcTheme }}"
>
    @include('layouts/layout-vertical')

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            @yield('content')
        </div>
    </div>
    <!-- [ Main Content ] end -->

    @include('layouts/footer-block')
    @include('layouts/customizer')

    {{-- Able Pro core scripts --}}
    @include('layouts/footer-js')

    {{-- =========================
       THEME PERSIST (HARUS setelah footer-js)
       ========================= --}}
    <script>
    (function () {
        const COOKIE = 'pc-theme';
        const LSKEY  = 'pc-theme';
        const MAX_AGE = 60 * 60 * 24 * 365; // 1 tahun

        function getCookie(name) {
            const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]+)'));
            return m ? decodeURIComponent(m[1]) : null;
        }

        function setCookie(name, value) {
            document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${MAX_AGE}; samesite=lax`;
        }

        function normalize(theme) {
            return theme === 'dark' ? 'dark' : 'light';
        }

        // Ambil theme aktif dari elemen yang mungkin dipakai Able Pro / Bootstrap
        function getCurrentTheme() {
            const bodyTheme = document.body.getAttribute('data-pc-theme');
            if (bodyTheme) return normalize(bodyTheme);

            const bsTheme = document.documentElement.getAttribute('data-bs-theme');
            if (bsTheme) return normalize(bsTheme);

            if (document.documentElement.classList.contains('dark')) return 'dark';

            return 'light';
        }

        function applyTheme(theme) {
            theme = normalize(theme);
            document.body.setAttribute('data-pc-theme', theme);
            // optional: kalau komponen kamu juga ikut bootstrap theme mode
            document.documentElement.setAttribute('data-bs-theme', theme);
        }

        function persist(theme) {
            theme = normalize(theme);
            localStorage.setItem(LSKEY, theme);
            setCookie(COOKIE, theme);

            // kalau Able Pro kamu ternyata baca key lain, tambahin di sini juga:
            // localStorage.setItem('pc_theme', theme);
            // localStorage.setItem('theme', theme);
        }

        // 1) Setelah semua script Able Pro jalan, paksa pakai theme tersimpan (localStorage > cookie)
        const saved = localStorage.getItem(LSKEY) || getCookie(COOKIE);
        if (saved) applyTheme(saved);

        // 2) Simpan kondisi akhir (kalau Able Pro sempat override)
        persist(getCurrentTheme());

        // 3) Jika theme berubah (via customizer Able Pro), simpan
        const obs = new MutationObserver(() => {
            persist(getCurrentTheme());
        });

        obs.observe(document.body, { attributes: true, attributeFilter: ['data-pc-theme', 'class'] });
        obs.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme', 'class'] });

        // 4) Saat pindah halaman / refresh, simpan theme terakhir
        window.addEventListener('beforeunload', () => persist(getCurrentTheme()));
    })();
    </script>

    @hasSection('scripts')
        @yield('scripts')
    @endif
    <script>
        if (localStorage.getItem('layout') === 'tab') {
            localStorage.removeItem('layout');
        }
    </script>
</body>
<!-- [Body] end -->
</html>
