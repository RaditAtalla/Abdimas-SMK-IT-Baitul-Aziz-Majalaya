<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasAjar;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index()
    {
        $kelasAjar = KelasAjar::query()
            ->join('tahun_ajaran', 'kelas_ajar.tahun_ajaran_id', '=', 'tahun_ajaran.tahun_ajaran_id')
            ->select('kelas_ajar.*')
            ->with(['kelas', 'tahunAjaran', 'waliKelas'])
            ->withCount('riwayatKelas')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('akademik.kelas.index', compact('kelasAjar'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        $tahunAjaran = TahunAjaran::orderByDesc('tahun_ajaran_id')->get();
        $waliKelas = User::role('Wali Kelas')->get();
        return view('akademik.kelas.create', compact('kelas', 'tahunAjaran', 'waliKelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,tahun_ajaran_id',
            'wali_user_id' => 'required|exists:users,id',
            'kkm'             => 'required|integer|min:0|max:100'
        ]);

        DB::beginTransaction();
        try {
            // Cari atau buat kelas berdasarkan nama_kelas
            $kelas = Kelas::firstOrCreate([
                'nama_kelas' => $request->nama_kelas,
            ]);

            // Cek duplikat kelas ajar (kelas_id + tahun_ajaran_id)
            $exists = KelasAjar::where('kelas_id', $kelas->kelas_id)
                ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                ->exists();
            if ($exists) {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors(['nama_kelas' => 'Kombinasi kelas dan tahun ajaran sudah ada!'])
                    ->withInput();
            }

            // Buat kelas ajar
            KelasAjar::create([
                'kelas_id' => $kelas->kelas_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'wali_user_id' => $request->wali_user_id,
                'kkm'             => (int) $request->kkm
            ]);

            DB::commit();
            return redirect()->route('akademik.kelas.index')->with('success', 'Kelas ajar berhasil ditambah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['nama_kelas' => 'Terjadi kesalahan. Gagal menambah kelas.'])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,tahun_ajaran_id',
            'wali_user_id' => 'required|exists:users,id',
            'kkm'             => 'required|integer|min:0|max:100'
        ]);

        DB::beginTransaction();
        try {
            $kelasAjar = KelasAjar::findOrFail($id);

            // Update nama kelas jika berubah
            $kelas = Kelas::firstOrCreate([
                'nama_kelas' => $request->nama_kelas,
            ]);

            // Cek duplikat kelas ajar (kelas_id + tahun_ajaran_id, kecuali diri sendiri)
            $exists = KelasAjar::where('kelas_id', $kelas->kelas_id)
                ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                ->where('kelas_ajar_id', '!=', $id)
                ->exists();
            if ($exists) {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors(['nama_kelas' => 'Kombinasi kelas dan tahun ajaran sudah ada!'])
                    ->withInput();
            }

            $kelasAjar->update([
                'kelas_id' => $kelas->kelas_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'wali_user_id' => $request->wali_user_id,
                'kkm'             => (int) $request->kkm
            ]);

            DB::commit();
            return redirect()->route('akademik.kelas.index')->with('success', 'Kelas ajar berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['nama_kelas' => 'Terjadi kesalahan. Gagal perbarui data kelas.'])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $kelasAjar = KelasAjar::findOrFail($id);

            // Cek apakah ada siswa di kelas ajar ini
            $adaSiswa = $kelasAjar->riwayatKelas()->exists();
            if ($adaSiswa) {
                DB::rollBack();
                return back()->with('error', 'Tidak dapat menghapus kelas ajar karena masih ada siswa di kelas ini.');
            }

            $kelas_id = $kelasAjar->kelas_id;

            // Hapus kelas ajar
            $kelasAjar->delete();

            // Cek apakah masih ada kelas ajar lain dengan kelas_id yang sama
            $remaining = KelasAjar::where('kelas_id', $kelas_id)->exists();

            // Jika tidak ada, hapus data di entitas Kelas
            if (!$remaining) {
                Kelas::where('kelas_id', $kelas_id)->delete();
            }

            DB::commit();
            return redirect()->route('akademik.kelas.index')->with('success', 'Kelas ajar berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Tidak dapat menghapus kelas ajar. Pastikan tidak ada data terkait.');
        }
    }
}
