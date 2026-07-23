<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MasterSiswaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $siswaQuery = Siswa::query()->with(['user', 'orangTua', 'kelurahan.kecamatan.kabupaten.provinsi']);

        if ($q) {
            $siswaQuery->where(function($query) use ($q) {
                $query->where('nama', 'like', "%$q%")
                      ->orWhere('nis', 'like', "%$q%")
                      ->orWhere('nisn', 'like', "%$q%");
            });
        }

        $siswa = $siswaQuery->orderByDesc('siswa_id')->paginate(20);
        return view('akademik.master_siswa.index', compact('siswa'));
    }

    public function create()
    {
        $orangTua = collect();
        $selectedOrtuId = session()->getOldInput('orang_tua_id');
        $selectedOrtu = null;

        if ($selectedOrtuId) {
            $selectedOrtu = OrangTua::with('kelurahan')->find($selectedOrtuId);
        }

        return view('akademik.master_siswa.create', compact('orangTua', 'selectedOrtu'));
    }

    public function store(Request $request)
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
            DB::transaction(function () use ($validated, $isOrtuBaru) {
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
                    $orangTua = OrangTua::findOrFail($validated['orang_tua_id']);
                }

                $alamatSiswa = !empty($validated['alamat_sama_ortu'])
                    ? $orangTua->jalan
                    : $validated['alamat'];

                $kelurahanSiswa = !empty($validated['alamat_sama_ortu'])
                    ? $orangTua->kelurahan_id
                    : $validated['kelurahan_id_hidden'];

                Siswa::create([
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
            });

            return redirect()
                ->route('akademik.master-siswa.index')
                ->with('success', 'Siswa berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function edit(Siswa $siswa)
    {
        $siswa->load([
            'user',
            'orangTua.kelurahan.kecamatan.kabupaten.provinsi',
            'kelurahan.kecamatan.kabupaten.provinsi',
            'tempatLahirKabupaten', 
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

        return view('akademik.master_siswa.edit', compact(
            'siswa',
            'orangTua',
            'tempatLahirLabel',
            'kelurahanLabel',
            'ortuKelurahanLabel'
        ));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $siswa->load(['user', 'orangTua.user']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
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
            ->route('akademik.master-siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        try {
            DB::beginTransaction();

            $user = $siswa->user;
            $siswa->delete();
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('akademik.master-siswa.index')
                ->with('success', 'Siswa berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('akademik.master-siswa.index')
                ->with('error', 'Terjadi kesalahan. Gagal menghapus siswa. Pastikan tidak ada data yang terhubung dengan siswa ini.');
        }
    }
}
