<li class="pc-item pc-caption">
    <label>Beranda</label>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('dashboard.akademik') }}" class="pc-link">
        <span class="pc-micon"><i class="bi bi-columns-gap"></i></span>
        <span class="pc-mtext">Beranda</span>
    </a>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('akademik.sekolah.index') }}" class="pc-link">
        <span class="pc-micon"><i class="bi bi-building"></i></span>
        <span class="pc-mtext">Sekolah</span>
    </a>
</li>

<li class="pc-item pc-caption">
    <label>Manajemen Akun</label>
    <svg class="pc-icon">
        <use xlink:href="#custom-flag"></use>
    </svg>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('akademik.staff.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-people"></i>
        </span>
        <span class="pc-mtext">Staff</span></a>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('akademik.master-siswa.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-person-badge"></i>
        </span>
        <span class="pc-mtext">Siswa</span></a>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('akademik.master-orang-tua.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-person-lines-fill"></i>
        </span>
        <span class="pc-mtext">Orang Tua</span></a>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('akademik.akun.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-person-check"></i>
        </span>
        <span class="pc-mtext">Aktivasi Akun</span></a>
</li>

<li class="pc-item pc-caption">
    <label>Manajemen Kelas</label>
    <svg class="pc-icon">
        <use xlink:href="#custom-flag"></use>
    </svg>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('akademik.tahun_ajaran.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-calendar-date"></i>
        </span>
        <span class="pc-mtext">Tahun Ajaran</span></a>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('akademik.kelas.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-mortarboard"></i>
        </span>
        <span class="pc-mtext">Daftar Kelas</span></a>
</li>
<li class="pc-item pc-caption">
    <label>Manajemen Mapel</label>
    <svg class="pc-icon">
        <use xlink:href="#custom-flag"></use>
    </svg>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('intrakurikuler.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-journal-bookmark"></i>
        </span>
        <span class="pc-mtext">Intrakurikuler</span></a>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('ekstrakurikuler.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-people"></i>
        </span>
        <span class="pc-mtext">Ekstrakurikuler</span></a>
</li>

<li class="pc-item pc-caption">
    <label>Absensi</label>
    <svg class="pc-icon">
        <use xlink:href="#custom-flag"></use>
    </svg>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('absensi.intrakurikuler.list') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-journal-bookmark"></i>
        </span>
        <span class="pc-mtext">Absensi Intrakurikuler</span></a>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('absensi.ekstrakurikuler.list') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-people"></i>
        </span>
        <span class="pc-mtext">Absensi Ekstrakurikuler</span></a>
</li>

<li class="pc-item pc-caption">
    <label>Cetak Dokumen</label>
    <svg class="pc-icon">
        <use xlink:href="#custom-flag"></use>
    </svg>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('dokumen.kelas') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-journal-bookmark"></i>
        </span>
        <span class="pc-mtext">Rapor</span></a>
</li>
<li class="pc-item pc-hasmenu">
    <a href="{{ route('dokumen.mutasi') }}" class="pc-link">
        <span class="pc-micon">
            <i class="bi bi-arrow-left-right"></i>
        </span>
        <span class="pc-mtext">Mutasi</span>
    </a>
</li>


{{-- <li class="pc-item pc-caption">
  <label>Other</label>
  <svg class="pc-icon">
    <use xlink:href="#custom-notification-status"></use>
  </svg>
</li>
<li class="pc-item pc-hasmenu">
  <a href="#!" class="pc-link"
    ><span class="pc-micon">
      <svg class="pc-icon">
        <use xlink:href="#custom-level"></use>
      </svg> </span
    ><span class="pc-mtext">Menu levels</span><span class="pc-arrow"><i data-feather="chevron-right"></i></span
  ></a>
  <ul class="pc-submenu">
    <li class="pc-item"><a class="pc-link" href="#!">Level 2.1</a></li>
    <li class="pc-item pc-hasmenu">
      <a href="#!" class="pc-link"
        >Level 2.2<span class="pc-arrow"><i data-feather="chevron-right"></i></span
      ></a>
      <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="#!">Level 3.1</a></li>
        <li class="pc-item"><a class="pc-link" href="#!">Level 3.2</a></li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link"
            >Level 3.3<span class="pc-arrow"><i data-feather="chevron-right"></i></span
          ></a>
          <ul class="pc-submenu">
            <li class="pc-item"><a class="pc-link" href="#!">Level 4.1</a></li>
            <li class="pc-item"><a class="pc-link" href="#!">Level 4.2</a></li>
          </ul>
        </li>
      </ul>
    </li>
    <li class="pc-item pc-hasmenu">
      <a href="#!" class="pc-link"
        >Level 2.3<span class="pc-arrow"><i data-feather="chevron-right"></i></span
      ></a>
      <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="#!">Level 3.1</a></li>
        <li class="pc-item"><a class="pc-link" href="#!">Level 3.2</a></li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link"
            >Level 3.3<span class="pc-arrow"><i data-feather="chevron-right"></i></span
          ></a>
          <ul class="pc-submenu">
            <li class="pc-item"><a class="pc-link" href="#!">Level 4.1</a></li>
            <li class="pc-item"><a class="pc-link" href="#!">Level 4.2</a></li>
          </ul>
        </li>
      </ul>
    </li>
  </ul>
</li> --}}