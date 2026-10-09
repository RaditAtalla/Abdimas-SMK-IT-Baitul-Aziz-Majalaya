<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KelasAjar;
use App\Models\RiwayatKelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SiswaController extends Controller
{
    public function index(KelasAjar $kelas_ajar)
    {
        $kelas_ajar->load(['kelas', 'tahunAjaran']);

        $riwayat = RiwayatKelas::query()
            ->where('kelas_ajar_id', $kelas_ajar->kelas_ajar_id)
            ->with(['siswa.user'])
            ->orderByDesc('riwayat_kelas_id')
            ->get();

        return view('akademik.siswa.index', compact('kelas_ajar', 'riwayat'));
    }

    public function create(KelasAjar $kelas_ajar)
    {
        $kelas_ajar->load(['kelas', 'tahunAjaran']);

        $orangTua = collect();

        // kalau ada old selected (form submit gagal), ambil 1 record ini saja untuk ditampilkan
        $selectedOrtuId = session()->getOldInput('orang_tua_id');
        $selectedOrtu = null;

        if ($selectedOrtuId) {
            $selectedOrtu = OrangTua::with('kelurahan')->find($selectedOrtuId);
        }

        return view('akademik.siswa.create', compact('kelas_ajar', 'orangTua', 'selectedOrtu'));
    }

    public function store(Request $request, KelasAjar $kelas_ajar)
    {
        $isOrtuBaru = empty($request->orang_tua_id);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('siswa', 'nis')],
            'nisn' => ['required', 'string', 'max:50', Rule::unique('siswa', 'nisn')],
            'jenis_kelamin' => ['required', Rule::in(['l', 'p'])],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'string', 'max:100'],
            'tempat_lahir_kabupaten_id' => ['required', 'exists:kabupaten,kabupaten_id'],
            'pendidikan_sebelumnya' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'kelurahan_id_hidden' => ['required', 'exists:kelurahan,kelurahan_id'],
            'alamat_sama_ortu' => ['nullable'],
        ];

        if ($isOrtuBaru) {
            $rules += [
                'ortu.nama_ayah' => ['required', 'string', 'max:255'],
                'ortu.nama_ibu' => ['required', 'string', 'max:255'],
                'ortu.pekerjaan_ayah' => ['required', 'string', 'max:255'],
                'ortu.pekerjaan_ibu' => ['required', 'string', 'max:255'],
                'ortu.jalan' => ['required', 'string', 'max:255'],
                'ortu.kelurahan_id' => ['required', 'exists:kelurahan,kelurahan_id'],
            ];
        } else {
            $rules += [
                'orang_tua_id' => ['required', 'exists:orang_tua,orang_tua_id'],
            ];
        }

        $validated = $request->validate($rules);

        try {
            DB::transaction(function () use ($validated, $kelas_ajar, $isOrtuBaru) {
                $userSiswa = User::create([
                    'name' => $validated['name'],
                    'username' => $validated['nis'], // username siswa = NIS
                    'email' => null,
                    'password' => Hash::make($validated['password']),
                ]);
                $userSiswa->assignRole('Siswa');
                if ($isOrtuBaru) {
                    $ortuUsername = 'ortu_' . $validated['nis'];
                    $suffix = 1;
                    $base = $ortuUsername;
                    while (User::where('username', $ortuUsername)->exists()) {
                        $ortuUsername = $base . '_' . $suffix;
                        $suffix++;
                    }

                    $userOrtu = User::create([
                        'name' => $validated['ortu']['nama_ayah'],
                        'username' => $ortuUsername,
                        'email' => null,
                        'password' => Hash::make($validated['password']),
                    ]);
                    $userOrtu->assignRole('Orang Tua');

                    $orangTua = OrangTua::create([
                        'user_id' => $userOrtu->id,
                        'nama_ayah' => $validated['ortu']['nama_ayah'],
                        'nama_ibu' => $validated['ortu']['nama_ibu'],
                        'pekerjaan_ayah' => $validated['ortu']['pekerjaan_ayah'],
                        'pekerjaan_ibu' => $validated['ortu']['pekerjaan_ibu'],
                        'jalan' => $validated['ortu']['jalan'],
                        'kelurahan_id' => $validated['ortu']['kelurahan_id'],
                    ]);
                } else {
                    $orangTuaLama = OrangTua::findOrFail($validated['orang_tua_id']);

                    $ortuUsername = 'ortu_' . $validated['nis'];
                    $suffix = 1;
                    $base = $ortuUsername;
                    while (User::where('username', $ortuUsername)->exists()) {
                        $ortuUsername = $base . '_' . $suffix;
                        $suffix++;
                    }

                    $userOrtu = User::create([
                        'name' => $orangTuaLama->nama_ayah,
                        'username' => $ortuUsername,
                        'email' => null,
                        'password' => Hash::make($validated['password']),
                    ]);

                    $orangTua = OrangTua::create([
                        'user_id' => $userOrtu->id,
                        'nama_ayah' => $orangTuaLama->nama_ayah,
                        'nama_ibu' => $orangTuaLama->nama_ibu,
                        'pekerjaan_ayah' => $orangTuaLama->pekerjaan_ayah,
                        'pekerjaan_ibu' => $orangTuaLama->pekerjaan_ibu,
                        'jalan' => $orangTuaLama->jalan,
                        'kelurahan_id' => $orangTuaLama->kelurahan_id
                    ]);
                }


                $alamatSiswa = !empty($validated['alamat_sama_ortu'])
                    ? $orangTua->jalan
                    : $validated['alamat'];

                $kelurahanSiswa = !empty($validated['alamat_sama_ortu'])
                    ? $orangTua->kelurahan_id
                    : $validated['kelurahan_id_hidden'];

                $siswa = Siswa::create([
                    'user_id' => $userSiswa->id,
                    'nis' => $validated['nis'],
                    'nisn' => $validated['nisn'],
                    'nama' => $validated['name'],
                    'jenis_kelamin' => $validated['jenis_kelamin'],
                    'tempat_lahir_kabupaten_id' => $validated['tempat_lahir_kabupaten_id'],
                    'tanggal_lahir' => $validated['tanggal_lahir'],
                    'agama' => $validated['agama'],
                    'pendidikan_sebelumnya' => $validated['pendidikan_sebelumnya'],
                    'alamat' => $alamatSiswa,
                    'orang_tua_id' => $orangTua->orang_tua_id,
                    'kelurahan_id' => $kelurahanSiswa,
                ]);

                // 5) masuk ke kelas ajar ini
                RiwayatKelas::create([
                    'siswa_id' => $siswa->siswa_id,
                    'kelas_ajar_id' => $kelas_ajar->kelas_ajar_id,
                ]);
            });

            return redirect()
                ->route('akademik.siswa.index', $kelas_ajar->kelas_ajar_id)
                ->with('success', 'Siswa berhasil ditambahkan ke kelas ini.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function edit(KelasAjar $kelas_ajar, Siswa $siswa)
    {
        $kelas_ajar->load(['kelas', 'tahunAjaran']);

        $siswa->load([
            'user',
            'orangTua.kelurahan.kecamatan.kabupaten.provinsi',
            'kelurahan.kecamatan.kabupaten.provinsi',
            'tempatLahirKabupaten', // kalau kamu punya relasi ini di model Siswa
        ]);

        $orangTua = OrangTua::query()
            ->with('kelurahan')
            ->orderByDesc('orang_tua_id')
            ->get();

        $tempatLahirLabel = $siswa->tempatLahirKabupaten?->nama ?? null;
        $kelurahanLabel   = $siswa->kelurahan?->nama
            ? ($siswa->kelurahan->nama . ' — ' . $siswa->kelurahan->kecamatan?->nama . ' (' . $siswa->kelurahan->kecamatan?->kabupaten?->nama . ')')
            : null;

        $ortuKelurahanLabel = $siswa->orangTua?->kelurahan?->nama
            ? ($siswa->orangTua->kelurahan->nama . ' — ' . $siswa->orangTua->kelurahan->kecamatan?->nama . ' (' . $siswa->orangTua->kelurahan->kecamatan?->kabupaten?->nama . ')')
            : null;

        return view('akademik.siswa.edit', compact(
            'kelas_ajar',
            'siswa',
            'orangTua',
            'tempatLahirLabel',
            'kelurahanLabel',
            'ortuKelurahanLabel'
        ));
    }

    public function update(Request $request, KelasAjar $kelas_ajar, Siswa $siswa)
    {
        $siswa->load(['user', 'orangTua.user']);

        $validated = $request->validate([
            // user siswa
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],

            // siswa
            'nis' => ['required', 'string', 'max:50', Rule::unique('siswa', 'nis')->ignore($siswa->siswa_id, 'siswa_id')],
            'nisn' => ['required', 'string', 'max:50', Rule::unique('siswa', 'nisn')->ignore($siswa->siswa_id, 'siswa_id')],
            'jenis_kelamin' => ['required', Rule::in(['l', 'p'])],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'string', 'max:100'],
            'tempat_lahir_kabupaten_id' => ['required', 'exists:kabupaten,kabupaten_id'],
            'pendidikan_sebelumnya' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],

            'kelurahan_id_hidden' => ['required', 'exists:kelurahan,kelurahan_id'],

            'ortu.nama_ayah' => ['required', 'string', 'max:255'],
            'ortu.nama_ibu' => ['required', 'string', 'max:255'],
            'ortu.pekerjaan_ayah' => ['required', 'string', 'max:255'],
            'ortu.pekerjaan_ibu' => ['required', 'string', 'max:255'],
            'ortu.jalan' => ['required', 'string', 'max:255'],
            'ortu.kelurahan_id' => ['required', 'exists:kelurahan,kelurahan_id'],

            'alamat_sama_ortu' => ['nullable'],
        ]);

        DB::transaction(function () use ($validated, $siswa) {

            $requestUsername = $validated['nis'];
            $existsUsername = User::where('username', $requestUsername)
                ->where('id', '!=', $siswa->user_id)
                ->exists();

            if ($existsUsername) {
                throw ValidationException::withMessages([
                    'nis' => 'NIS ini sudah dipakai sebagai username akun lain.',
                ]);
            }

            $payloadUser = [
                'name' => $validated['name'],
                'username' => $validated['nis'],
            ];

            if (!empty($validated['password'])) {
                $payloadUser['password'] = Hash::make($validated['password']);
            }

            $siswa->user->update($payloadUser);

            if (!$siswa->orangTua) {
                throw ValidationException::withMessages([
                    'ortu' => 'Data orang tua tidak ditemukan untuk siswa ini.',
                ]);
            }

            $ortuInput = $validated['ortu'];

            $siswa->orangTua->update([
                'nama_ayah' => $ortuInput['nama_ayah'],
                'nama_ibu' => $ortuInput['nama_ibu'],
                'pekerjaan_ayah' => $ortuInput['pekerjaan_ayah'],
                'pekerjaan_ibu' => $ortuInput['pekerjaan_ibu'],
                'jalan' => $ortuInput['jalan'],
                'kelurahan_id' => $ortuInput['kelurahan_id'],
            ]);

            if ($siswa->orangTua->user) {
                $payloadUserOrtu = [
                    'name' => $ortuInput['nama_ayah'],
                ];
                if (!empty($validated['password'])) {
                    $payloadUserOrtu['password'] = Hash::make($validated['password']);
                }
                $siswa->orangTua->user->update($payloadUserOrtu);
            }

            $alamatSiswa = $validated['alamat'];
            $kelurahanSiswa = $validated['kelurahan_id_hidden'];

            if (!empty($validated['alamat_sama_ortu'])) {
                $alamatSiswa = $siswa->orangTua->jalan;
                $kelurahanSiswa = $siswa->orangTua->kelurahan_id;
            }

            $siswa->update([
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'],
                'nama' => $validated['name'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tempat_lahir_kabupaten_id' => $validated['tempat_lahir_kabupaten_id'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'agama' => $validated['agama'],
                'pendidikan_sebelumnya' => $validated['pendidikan_sebelumnya'],
                'alamat' => $alamatSiswa,
                'kelurahan_id' => $kelurahanSiswa,
                'orang_tua_id' => $siswa->orang_tua_id,
            ]);
        });

        return redirect()
            ->route('akademik.siswa.index', $kelas_ajar->kelas_ajar_id)
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(KelasAjar $kelas_ajar, Siswa $siswa)
    {
        try {
            DB::beginTransaction();

            // Cek apakah siswa masih punya riwayat kelas lain (opsional, jika ingin tiap siswa minimal 1 riwayat)
            // $riwayatCount = RiwayatKelas::where('siswa_id', $siswa->siswa_id)->count();
            // if ($riwayatCount <= 1) {
            //     return redirect()->route('akademik.siswa.index', $kelas_ajar->kelas_ajar_id)
            //         ->with('error', 'Siswa minimal harus memiliki satu riwayat kelas.');
            // }

            $deleted = RiwayatKelas::where('kelas_ajar_id', $kelas_ajar->kelas_ajar_id)
                ->where('siswa_id', $siswa->siswa_id)
                ->delete();

            DB::commit();

            if ($deleted) {
                return redirect()->route('akademik.siswa.index', $kelas_ajar->kelas_ajar_id)
                    ->with('success', 'Siswa berhasil dikeluarkan dari kelas ini.');
            } else {
                return redirect()->route('akademik.siswa.index', $kelas_ajar->kelas_ajar_id)
                    ->with('error', 'Gagal mengeluarkan siswa dari kelas.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('akademik.siswa.index', $kelas_ajar->kelas_ajar_id)
                // ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
                ->with('error', 'Terjadi kesalahan. Gagal menghapus siswa');
        }
    }

    public function ajaxSearchSiswa(Request $request, KelasAjar $kelas_ajar)
    {
        $q = $request->input('q');
        $tahunAjaranId = $kelas_ajar->tahun_ajaran_id;

        // Cari siswa yang belum ada di kelas_ajar ini
        $siswaQuery = Siswa::query()
            ->whereDoesntHave('riwayatKelas', function ($q3) use ($kelas_ajar) {
                $q3->where('kelas_ajar_id', $kelas_ajar->kelas_ajar_id);
            })
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%$q%")
                    ->orWhere('nis', 'like', "%$q%")
                    ->orWhere('nisn', 'like', "%$q%");
            });

        $results = $siswaQuery->get()->map(function ($siswa) {
            $rk = $siswa->riwayatKelas()->latest('riwayat_kelas_id')->first();
            $kelas = $rk?->kelasAjar?->kelas?->nama_kelas ?? '-';
            $tahun = $rk?->kelasAjar?->tahunAjaran?->tahun ?? '-';
            $semester = $rk?->kelasAjar?->tahunAjaran?->semester ?? '-';

            return [
                'id' => $siswa->siswa_id,
                'text' => "{$siswa->nama} | Kelas: {$kelas} | Tahun: {$tahun} {$semester} | NIS: {$siswa->nis} | NISN: {$siswa->nisn}"
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function addExistingSiswa(Request $request, KelasAjar $kelas_ajar)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,siswa_id',
        ]);

        // Cek apakah sudah ada di kelas ini
        $exists = RiwayatKelas::where('kelas_ajar_id', $kelas_ajar->kelas_ajar_id)
            ->where('siswa_id', $request->siswa_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['siswa_id' => 'Siswa sudah terdaftar di kelas ini.']);
        }

        // Cek apakah siswa sudah punya kelas lain di tahun ajaran & semester yang sama
        $tahunAjaranId = $kelas_ajar->tahun_ajaran_id;
        $semester = $kelas_ajar->tahunAjaran->semester;

        $sudahAdaDiTahunAjaranIni = RiwayatKelas::where('siswa_id', $request->siswa_id)
            ->whereHas('kelasAjar', function ($q) use ($tahunAjaranId, $semester) {
                $q->where('tahun_ajaran_id', $tahunAjaranId)
                    ->whereHas('tahunAjaran', function ($q2) use ($semester) {
                        $q2->where('semester', $semester);
                    });
            })
            ->exists();

        if ($sudahAdaDiTahunAjaranIni) {
            return back()->withErrors(['siswa_id' => 'Siswa sudah terdaftar di kelas lain pada tahun ajaran & semester ini.']);
        }

        // Tambahan: Cek tidak bisa memindahkan ke tahun ajaran/semester yang sama atau lebih rendah
        $kelasTujuan = $kelas_ajar->load('tahunAjaran');
        $tahunTujuan = $kelasTujuan->tahunAjaran->tahun;
        $semesterTujuan = $kelasTujuan->tahunAjaran->semester;

        // Ambil riwayat kelas terakhir siswa
        $riwayatTerakhir = RiwayatKelas::where('siswa_id', $request->siswa_id)
            ->with('kelasAjar.tahunAjaran')
            ->orderByDesc('riwayat_kelas_id')
            ->first();

        if ($riwayatTerakhir) {
            $tahunAsal = $riwayatTerakhir->kelasAjar->tahunAjaran->tahun;
            $semesterAsal = $riwayatTerakhir->kelasAjar->tahunAjaran->semester;

            // Fungsi bantu untuk dapatkan tahun awal (misal: "2025/2026" -> 2025)
            $getTahunAwal = function ($tahunStr) {
                return (int)explode('/', $tahunStr)[0];
            };

            $tahunTujuanInt = $getTahunAwal($tahunTujuan);
            $tahunAsalInt = $getTahunAwal($tahunAsal);

            $semesterMap = [
                'Ganjil' => 1,
                'Genap' => 2,
            ];
            $semTujuan = $semesterMap[$semesterTujuan] ?? 0;
            $semAsal = $semesterMap[$semesterAsal] ?? 0;

            if (
                $tahunTujuanInt < $tahunAsalInt ||
                ($tahunTujuanInt == $tahunAsalInt && $semTujuan <= $semAsal)
            ) {
                return back()->withErrors(['siswa_id' => 'Tidak bisa memindahkan ke tahun ajaran/semester yang sama atau lebih rendah dari riwayat terakhir siswa.']);
            }
        }

        // Tambahkan ke kelas_ajar (naik kelas)
        RiwayatKelas::create([
            'siswa_id' => $request->siswa_id,
            'kelas_ajar_id' => $kelas_ajar->kelas_ajar_id,
        ]);

        return back()->with('success', 'Siswa berhasil dimasukkan ke kelas.');
    }

    public function pindahkanSiswa(Request $request, KelasAjar $kelas_ajar, Siswa $siswa)
    {
        $validated = $request->validate([
            'tujuan_kelas_ajar_id' => 'required|exists:kelas_ajar,kelas_ajar_id',
        ]);

        try {
            DB::transaction(function () use ($validated, $kelas_ajar, $siswa) {
                $riwayatAsal = RiwayatKelas::where('kelas_ajar_id', $kelas_ajar->kelas_ajar_id)
                    ->where('siswa_id', $siswa->siswa_id)
                    ->first();

                if (!$riwayatAsal) {
                    throw ValidationException::withMessages([
                        'tujuan_kelas_ajar_id' => 'Siswa tidak terdaftar di kelas ini.',
                    ]);
                }

                $kelasTujuan = KelasAjar::with('tahunAjaran')->findOrFail($validated['tujuan_kelas_ajar_id']);
                if ($kelasTujuan->kelas_ajar_id === $kelas_ajar->kelas_ajar_id) {
                    throw ValidationException::withMessages([
                        'tujuan_kelas_ajar_id' => 'Kelas tujuan harus berbeda dari kelas saat ini.',
                    ]);
                }

                $sudahAdaDiKelasTujuan = RiwayatKelas::where('kelas_ajar_id', $kelasTujuan->kelas_ajar_id)
                    ->where('siswa_id', $siswa->siswa_id)
                    ->exists();
                if ($sudahAdaDiKelasTujuan) {
                    throw ValidationException::withMessages([
                        'tujuan_kelas_ajar_id' => 'Siswa sudah terdaftar di kelas tujuan.',
                    ]);
                }

                $tahunAsal = (int) explode('/', $kelas_ajar->load('tahunAjaran')->tahunAjaran->tahun)[0];
                $tahunTujuan = (int) explode('/', $kelasTujuan->tahunAjaran->tahun)[0];
                $semesterMap = ['Ganjil' => 1, 'Genap' => 2];
                $semAsal = $semesterMap[$kelas_ajar->tahunAjaran->semester] ?? 0;
                $semTujuan = $semesterMap[$kelasTujuan->tahunAjaran->semester] ?? 0;

                if ($tahunTujuan < $tahunAsal || ($tahunTujuan === $tahunAsal && $semTujuan <= $semAsal)) {
                    throw ValidationException::withMessages([
                        'tujuan_kelas_ajar_id' => 'Tidak bisa memindahkan ke tahun ajaran/semester yang sama atau lebih rendah.',
                    ]);
                }

                $riwayatAsal->update(['kelas_ajar_id' => $kelasTujuan->kelas_ajar_id]);
            });

            return redirect()->route('akademik.siswa.index', $kelas_ajar->kelas_ajar_id)
                ->with('success', 'Siswa berhasil dipindahkan ke kelas tujuan.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'tujuan_kelas_ajar_id' => 'Terjadi kesalahan. Gagal memindahkan siswa.',
            ]);
        }
    }

    public function ajaxSearchKelas(Request $request)
    {
        $q = $request->input('q');
        $showOtherSemester = $request->input('show_other_semester') === 'true';
        $showOtherTahun = $request->input('show_other_tahun') === 'true';
        $kelasAjarId = $request->input('kelas_ajar_id');

        $query = KelasAjar::with(['kelas', 'tahunAjaran'])->withCount('riwayatKelas');

        if ($kelasAjarId) {
            $kelasTujuan = KelasAjar::with('tahunAjaran')->find($kelasAjarId);
            if ($kelasTujuan && $kelasTujuan->tahunAjaran) {
                $tahun = $kelasTujuan->tahunAjaran->tahun;
                $semester = $kelasTujuan->tahunAjaran->semester;

                // Target default untuk load siswa (mengambil dari kelas sebelumnya)
                $targetTahun = $tahun;
                $targetSemester = $semester === 'Ganjil' ? 'Genap' : 'Ganjil';
                
                if ($semester === 'Ganjil') {
                    // Jika saat ini ganjil, maka default-nya adalah genap tahun sebelumnya
                    $parts = explode('/', $tahun);
                    if (count($parts) == 2) {
                        $targetTahun = ((int)$parts[0] - 1) . '/' . ((int)$parts[1] - 1);
                    }
                }

                if (!$showOtherTahun) {
                    $query->whereHas('tahunAjaran', function($q2) use ($targetTahun) {
                        $q2->where('tahun', $targetTahun);
                    });
                }
                
                if (!$showOtherSemester) {
                    $query->whereHas('tahunAjaran', function($q2) use ($targetSemester) {
                        $q2->where('semester', $targetSemester);
                    });
                }
            }
        }

        $kelasAjar = $query->where(function($subQuery) use ($q) {
                if ($q) {
                    $subQuery->whereHas('kelas', function ($q2) use ($q) {
                        $q2->where('nama_kelas', 'like', "%$q%");
                    })->orWhereHas('tahunAjaran', function ($q3) use ($q) {
                        $q3->where('tahun', 'like', "%$q%")->orWhere('semester', 'like', "%$q%");
                    });
                }
            })
            ->limit(20)
            ->get();

        $results = $kelasAjar->map(function ($ka) {
            return [
                'id' => $ka->kelas_ajar_id,
                'text' => "{$ka->kelas->nama_kelas} - {$ka->tahunAjaran->tahun} {$ka->tahunAjaran->semester} ({$ka->riwayat_kelas_count} Siswa)"
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function showLoadSiswaForm($kelas_ajar_id, Request $request)
    {
        $kelasTujuan = KelasAjar::with(['kelas', 'tahunAjaran'])->findOrFail($kelas_ajar_id);
        $kelasAsalId = $request->input('kelas_asal_id');
        $siswaList = [];
        $kelasAsal = null;

        if ($kelasAsalId) {
            $riwayat = RiwayatKelas::where('kelas_ajar_id', $kelasAsalId)
                ->with(['siswa.user'])
                ->get();
            $siswaList = $riwayat->map(function ($r) {
                return $r->siswa;
            });

            $kelasAsal = KelasAjar::query()->with(['kelas', 'tahunAjaran'])->find($kelasAsalId);
        }

        return view('akademik.siswa.load_siswa', compact('kelasTujuan', 'kelasAsalId', 'siswaList', 'kelasAsal'));
    }

    public function loadSiswaFromKelas(Request $request, $kelas_ajar_id)
    {
        $request->validate([
            'kelas_asal_id' => 'required|exists:kelas_ajar,kelas_ajar_id',
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,siswa_id',
        ]);

        try {
            DB::beginTransaction();

            $kelasTujuan = KelasAjar::with('tahunAjaran')->findOrFail($kelas_ajar_id);
            $kelasAsal = KelasAjar::with('tahunAjaran')->findOrFail($request->kelas_asal_id);

            function getTahunAwal($tahunStr)
            {
                return (int)explode('/', $tahunStr)[0];
            }

            $tahunTujuan = getTahunAwal($kelasTujuan->tahunAjaran->tahun);
            $tahunAsal = getTahunAwal($kelasAsal->tahunAjaran->tahun);
            $semesterTujuan = $kelasTujuan->tahunAjaran->semester;
            $semesterAsal = $kelasAsal->tahunAjaran->semester;

            $semesterMap = [
                'Ganjil' => 1,
                'Genap' => 2,
            ];
            $semTujuan = $semesterMap[$semesterTujuan] ?? 0;
            $semAsal = $semesterMap[$semesterAsal] ?? 0;

            if (
                $tahunTujuan < $tahunAsal ||
                ($tahunTujuan == $tahunAsal && $semTujuan <= $semAsal)
            ) {
                DB::rollBack();
                return back()->withErrors(['kelas_asal_id' => 'Tidak bisa memindahkan ke tahun ajaran/semester yang sama atau lebih rendah.']);
            }

            $count = 0;
            $tahunAjaranId = $kelasTujuan->tahun_ajaran_id;
            foreach ($request->siswa_ids as $siswaId) {
                $sudahAda = RiwayatKelas::where('siswa_id', $siswaId)
                    ->whereHas('kelasAjar', function ($q) use ($tahunAjaranId) {
                        $q->where('tahun_ajaran_id', $tahunAjaranId);
                    })->exists();
                if (!$sudahAda) {
                    RiwayatKelas::create([
                        'siswa_id' => $siswaId,
                        'kelas_ajar_id' => $kelas_ajar_id,
                    ]);
                    $count++;
                }
            }

            if ($count <= 0) {
                DB::commit();
                return redirect()->route('akademik.siswa.index', $kelas_ajar_id)
                    ->with('warning', "$count siswa dimasukkan ke kelas. Semua siswa dari kelas asal sudah terdaftar di kelas tujuan pada tahun ajaran & semester ini.");
            }

            DB::commit();
            return redirect()->route('akademik.siswa.index', $kelas_ajar_id)
                ->with('success', "$count siswa berhasil dimasukkan ke kelas.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan. Gagal memuat data siswa']);
        }
    }

    public function ajaxSearchOrangTua(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $page = max((int) $request->get('page', 1), 1);
        $perPage = 20;

        $query = OrangTua::query()
            ->with('kelurahan')
            ->orderByDesc('orang_tua_id');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama_ayah', 'like', "%{$q}%")
                    ->orWhere('nama_ibu', 'like', "%{$q}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $results = $paginator->getCollection()->map(function ($ot) {
            return [
                'id' => $ot->orang_tua_id,
                'text' => trim(($ot->nama_ayah ?? '') . ' / ' . ($ot->nama_ibu ?? '')),
                'jalan' => $ot->jalan ?? '',
                'kelurahan_id' => $ot->kelurahan_id ?? '',
                'kelurahan_label' => $ot->kelurahan->nama ?? '',
            ];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $paginator->hasMorePages(),
            ],
        ]);
    }
}
