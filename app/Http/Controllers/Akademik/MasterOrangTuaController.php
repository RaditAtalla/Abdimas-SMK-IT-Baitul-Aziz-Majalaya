<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrangTua;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MasterOrangTuaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $ortuQuery = OrangTua::query()->with(['user', 'kelurahan.kecamatan.kabupaten.provinsi']);

        if ($q) {
            $ortuQuery->where(function($query) use ($q) {
                $query->where('nama_ayah', 'like', "%$q%")
                      ->orWhere('nama_ibu', 'like', "%$q%");
            });
        }

        $orangTua = $ortuQuery->orderByDesc('orang_tua_id')->paginate(20);
        return view('akademik.master_orangtua.index', compact('orangTua'));
    }

    public function edit(OrangTua $orangTua)
    {
        $orangTua->load([
            'user',
            'kelurahan.kecamatan.kabupaten.provinsi',
        ]);

        $ortuKelurahanLabel = $orangTua->kelurahan?->nama
            ? ($orangTua->kelurahan->nama . ' — ' . $orangTua->kelurahan->kecamatan?->nama . ' (' . $orangTua->kelurahan->kecamatan?->kabupaten?->nama . ')')
            : null;

        return view('akademik.master_orangtua.edit', compact(
            'orangTua',
            'ortuKelurahanLabel'
        ));
    }

    public function update(Request $request, OrangTua $orangTua)
    {
        $orangTua->load(['user']);

        $validated = $request->validate([
            'nama_ayah' => ['required', 'string', 'max:255'],
            'nama_ibu' => ['required', 'string', 'max:255'],
            'pekerjaan_ayah' => ['required', 'string', 'max:255'],
            'pekerjaan_ibu' => ['required', 'string', 'max:255'],
            'jalan' => ['required', 'string', 'max:255'],
            'kelurahan_id' => ['required', 'exists:kelurahan,kelurahan_id'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated, $orangTua) {
            $orangTua->update([
                'nama_ayah' => $validated['nama_ayah'],
                'nama_ibu' => $validated['nama_ibu'],
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'],
                'pekerjaan_ibu' => $validated['pekerjaan_ibu'],
                'jalan' => $validated['jalan'],
                'kelurahan_id' => $validated['kelurahan_id'],
            ]);

            if ($orangTua->user) {
                $payloadUserOrtu = [
                    'name' => $validated['nama_ayah'],
                ];
                if (!empty($validated['password'])) {
                    $payloadUserOrtu['password'] = Hash::make($validated['password']);
                }
                $orangTua->user->update($payloadUserOrtu);
            }
        });

        return redirect()
            ->route('akademik.master-orang-tua.index')
            ->with('success', 'Data orang tua berhasil diperbarui.');
    }

    public function destroy(OrangTua $orangTua)
    {
        try {
            DB::beginTransaction();

            $user = $orangTua->user;
            $orangTua->delete();
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('akademik.master-orang-tua.index')
                ->with('success', 'Data orang tua berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('akademik.master-orang-tua.index')
                ->with('error', 'Terjadi kesalahan. Gagal menghapus orang tua. Pastikan tidak ada data siswa yang terhubung dengan orang tua ini.');
        }
    }
}
