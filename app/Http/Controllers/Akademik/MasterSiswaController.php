<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kabupaten;
use App\Models\Kelurahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

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

    /**
     * Download template Excel untuk import data siswa (Solusi 1: Teks Biasa & Ringkas).
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Siswa');

        // Title & Instruction
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA MASTER SISWA & ORANG TUA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Petunjuk: Isikan data siswa mulai dari baris 5. Cukup ketik NAMA daerah (misal: Tempat Lahir "Bandung", Kelurahan "Majalaya") tanpa perlu kode ID angka.');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF555555'));

        // Headers at Row 4 (15 Kolom Ringkas)
        $headers = [
            'NIS*', 'NISN*', 'Nama Siswa*', 'Jenis Kelamin (l/p)*',
            'Tempat Lahir (Kabupaten/Kota)', 'Tgl Lahir (YYYY-MM-DD)*', 'Agama*', 'Sekolah Asal*',
            'Nama Ayah*', 'Nama Ibu*', 'Pekerjaan Ayah', 'Pekerjaan Ibu',
            'Alamat (Jalan/RT/RW)*', 'Nama Kelurahan/Desa', 'Password (Opsional)'
        ];

        $cols = range('A', 'O');
        foreach ($headers as $index => $header) {
            $col = $cols[$index];
            $sheet->setCellValue("{$col}4", $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header Styling
        $sheet->getStyle('A4:O4')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A4:O4')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A4:O4')->getFill()->getStartColor()->setARGB('FF2B579A');
        $sheet->getStyle('A4:O4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Contoh Data pada Baris 5 (Teks Biasa)
        $sampleData = [
            '25RPL1099', '1002500099', 'Siswa Contoh', 'l',
            'Bandung', '2008-05-20', 'Islam', 'SMPN 1 Majalaya',
            'Budi Hidayat', 'Siti Rahma', 'Wiraswasta', 'Ibu Rumah Tangga',
            'Jl. Raya Majalaya No. 123', 'Majalaya', 'secret123'
        ];

        foreach ($sampleData as $index => $val) {
            $col = $cols[$index];
            $sheet->setCellValueExplicit("{$col}5", $val, DataType::TYPE_STRING);
        }

        $sheet->getStyle('A5:O5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:O5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $filename = "Template_Import_Siswa_SMK_IT_Baitul_Aziz.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Preview Data Excel sebelum disetujui & disimpan.
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:5120',
        ], [
            'file_excel.required' => 'Pilih file Excel yang akan diunggah.',
            'file_excel.mimes' => 'Format file harus berupa Excel (.xlsx atau .xls).',
            'file_excel.max' => 'Ukuran file tidak boleh melebihi 5MB.',
        ]);

        $file = $request->file('file_excel');

        $defaultKab = Kabupaten::where('nama', 'like', '%Bandung%')->first() 
            ?? Kabupaten::first();
        $defaultKel = Kelurahan::where('nama', 'like', '%Majalaya%')->first() 
            ?? Kelurahan::first();

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            $previewRows = [];
            $countNew = 0;
            $countUpdate = 0;

            for ($row = 5; $row <= $highestRow; $row++) {
                $nis = trim($sheet->getCell("A{$row}")->getValue());
                $nisn = trim($sheet->getCell("B{$row}")->getValue());
                $nama = trim($sheet->getCell("C{$row}")->getValue());
                $jk = strtolower(trim($sheet->getCell("D{$row}")->getValue()));
                $kabText = trim($sheet->getCell("E{$row}")->getValue());
                
                $rawTglCell = $sheet->getCell("F{$row}");
                $tglLahir = $this->parseExcelDate($rawTglCell, $rawTglCell->getValue());

                $agama = trim($sheet->getCell("G{$row}")->getValue());
                $pendidikanPrev = trim($sheet->getCell("H{$row}")->getValue());
                $namaAyah = trim($sheet->getCell("I{$row}")->getValue());
                $namaIbu = trim($sheet->getCell("J{$row}")->getValue());
                $pekAyah = trim($sheet->getCell("K{$row}")->getValue());
                $pekIbu = trim($sheet->getCell("L{$row}")->getValue());
                $alamat = trim($sheet->getCell("M{$row}")->getValue());
                $kelText = trim($sheet->getCell("N{$row}")->getValue());
                $rawPassword = trim($sheet->getCell("O{$row}")->getValue());

                // Lewati baris kosong
                if (empty($nis) && empty($nama)) {
                    continue;
                }

                // Smart Text Match Tempat Lahir
                $finalKabId = $defaultKab?->kabupaten_id;
                $kabLabel = $defaultKab?->nama ?? '-';
                if (!empty($kabText)) {
                    $foundKab = Kabupaten::where('nama', 'like', "%{$kabText}%")->first();
                    if ($foundKab) {
                        $finalKabId = $foundKab->kabupaten_id;
                        $kabLabel = $foundKab->nama;
                    }
                }

                // Smart Text Match Kelurahan Domisili
                $finalKelId = $defaultKel?->kelurahan_id;
                $kelLabel = $defaultKel?->nama ?? '-';
                if (!empty($kelText)) {
                    $foundKel = Kelurahan::where('nama', 'like', "%{$kelText}%")->first();
                    if ($foundKel) {
                        $finalKelId = $foundKel->kelurahan_id;
                        $kelLabel = $foundKel->nama;
                    }
                }

                // Cek status keberadaan di database
                $existingSiswa = Siswa::where('nis', $nis)->orWhere('nisn', $nisn)->first();
                $isUpdate = !empty($existingSiswa);

                if ($isUpdate) {
                    $countUpdate++;
                } else {
                    $countNew++;
                }

                $previewRows[] = [
                    'row' => $row,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'jk' => in_array($jk, ['l', 'p']) ? $jk : 'l',
                    'kab_text' => $kabText,
                    'final_kab_id' => $finalKabId,
                    'kab_label' => $kabLabel,
                    'tgl_lahir' => $tglLahir,
                    'agama' => !empty($agama) ? $agama : 'Islam',
                    'pendidikan_prev' => !empty($pendidikanPrev) ? $pendidikanPrev : '-',
                    'nama_ayah' => !empty($namaAyah) ? $namaAyah : '-',
                    'nama_ibu' => !empty($namaIbu) ? $namaIbu : '-',
                    'pek_ayah' => !empty($pekAyah) ? $pekAyah : '-',
                    'pek_ibu' => !empty($pekIbu) ? $pekIbu : '-',
                    'alamat' => !empty($alamat) ? $alamat : '-',
                    'kel_text' => $kelText,
                    'final_kel_id' => $finalKelId,
                    'kel_label' => $kelLabel,
                    'password' => !empty($rawPassword) ? $rawPassword : $nis,
                    'is_update' => $isUpdate,
                    'status_badge' => $isUpdate ? 'warning' : 'success',
                    'status_text' => $isUpdate ? 'Perbarui Data' : 'Siswa Baru',
                ];
            }

            if (empty($previewRows)) {
                return redirect()->back()->with('error', 'File Excel tidak berisi data siswa yang valid.');
            }

            session(['master_siswa_import_data' => $previewRows]);

            return view('akademik.master_siswa.preview_import', compact('previewRows', 'countNew', 'countUpdate'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Konfirmasi dan Eksekusi Simpan Data Excel ke Database (Menerima Suntingan Interaktif Form).
     */
    public function confirmImport(Request $request)
    {
        $submittedRows = $request->input('rows', []);

        if (empty($submittedRows) || !is_array($submittedRows)) {
            return redirect()->route('akademik.master-siswa.index')
                ->with('error', 'Tidak ada data siswa yang dikirimkan untuk disimpan.');
        }

        $defaultKabId = Kabupaten::where('nama', 'like', '%Bandung%')->first()?->kabupaten_id 
            ?? (Kabupaten::first()?->kabupaten_id ?? '32.04');

        $defaultKelId = Kelurahan::where('nama', 'like', '%Majalaya%')->first()?->kelurahan_id 
            ?? (Kelurahan::first()?->kelurahan_id ?? '32.04.33.2001');

        try {
            DB::beginTransaction();

            $newCount = 0;
            $updateCount = 0;

            foreach ($submittedRows as $item) {
                $nis = trim($item['nis'] ?? '');
                $nisn = trim($item['nisn'] ?? '');
                $nama = trim($item['nama'] ?? '');
                $jk = strtolower(trim($item['jk'] ?? 'l'));
                $kabText = trim($item['kab_text'] ?? '');
                $tglLahir = $this->parseExcelDate(null, $item['tgl_lahir'] ?? '');
                $agama = trim($item['agama'] ?? 'Islam');
                $pendidikanPrev = trim($item['pendidikan_prev'] ?? '-');
                $namaAyah = trim($item['nama_ayah'] ?? '-');
                $namaIbu = trim($item['nama_ibu'] ?? '-');
                $pekAyah = trim($item['pek_ayah'] ?? '-');
                $pekIbu = trim($item['pek_ibu'] ?? '-');
                $alamat = trim($item['alamat'] ?? '-');
                $kelText = trim($item['kel_text'] ?? '');
                $rawPassword = trim($item['password'] ?? '');

                if (empty($nis) || empty($nama)) {
                    continue;
                }

                // Smart Text Match untuk Tempat Lahir
                $finalKabId = $defaultKabId;
                if (!empty($kabText)) {
                    $foundKab = Kabupaten::where('nama', 'like', "%{$kabText}%")->first();
                    if ($foundKab) {
                        $finalKabId = $foundKab->kabupaten_id;
                    }
                }

                // Smart Text Match untuk Kelurahan Domisili
                $finalKelId = $defaultKelId;
                if (!empty($kelText)) {
                    $foundKel = Kelurahan::where('nama', 'like', "%{$kelText}%")->first();
                    if ($foundKel) {
                        $finalKelId = $foundKel->kelurahan_id;
                    }
                }

                $password = !empty($rawPassword) ? $rawPassword : $nis;

                $siswa = Siswa::where('nis', $nis)->orWhere('nisn', $nisn)->first();

                if ($siswa) {
                    // ===== MODE UPDATE =====
                    if ($siswa->user) {
                        $siswa->user->update([
                            'name' => $nama,
                            'username' => $nis,
                            'password' => Hash::make($password),
                        ]);
                    }

                    if ($siswa->orangTua) {
                        $siswa->orangTua->update([
                            'nama_ayah' => !empty($namaAyah) ? $namaAyah : '-',
                            'nama_ibu' => !empty($namaIbu) ? $namaIbu : '-',
                            'pekerjaan_ayah' => !empty($pekAyah) ? $pekAyah : '-',
                            'pekerjaan_ibu' => !empty($pekIbu) ? $pekIbu : '-',
                            'jalan' => !empty($alamat) ? $alamat : '-',
                            'kelurahan_id' => $finalKelId,
                        ]);

                        if ($siswa->orangTua->user) {
                            $siswa->orangTua->user->update([
                                'name' => !empty($namaAyah) && $namaAyah !== '-' ? $namaAyah : "Ortu $nama",
                            ]);
                        }
                    }

                    $siswa->update([
                        'nis' => $nis,
                        'nisn' => $nisn,
                        'nama' => $nama,
                        'jenis_kelamin' => in_array($jk, ['l', 'p']) ? $jk : 'l',
                        'tempat_lahir_kabupaten_id' => $finalKabId,
                        'tanggal_lahir' => $tglLahir,
                        'agama' => $agama,
                        'pendidikan_sebelumnya' => $pendidikanPrev,
                        'alamat' => $alamat,
                        'kelurahan_id' => $finalKelId,
                    ]);

                    $updateCount++;
                } else {
                    // ===== MODE CREATE BARU =====
                    $userSiswa = User::create([
                        'name' => $nama,
                        'username' => $nis,
                        'email' => null,
                        'password' => Hash::make($password),
                    ]);
                    $userSiswa->assignRole('Siswa');

                    $ortuUsername = 'ortu_' . $nis;
                    $suffix = 1;
                    $baseUsername = $ortuUsername;
                    while (User::where('username', $ortuUsername)->exists()) {
                        $ortuUsername = $baseUsername . '_' . $suffix++;
                    }

                    $userOrtu = User::create([
                        'name' => !empty($namaAyah) && $namaAyah !== '-' ? $namaAyah : "Ortu $nama",
                        'username' => $ortuUsername,
                        'email' => null,
                        'password' => Hash::make($password),
                    ]);
                    $userOrtu->assignRole('Orang Tua');

                    $orangTua = OrangTua::create([
                        'user_id' => $userOrtu->id,
                        'nama_ayah' => !empty($namaAyah) ? $namaAyah : '-',
                        'nama_ibu' => !empty($namaIbu) ? $namaIbu : '-',
                        'pekerjaan_ayah' => !empty($pekAyah) ? $pekAyah : '-',
                        'pekerjaan_ibu' => !empty($pekIbu) ? $pekIbu : '-',
                        'jalan' => !empty($alamat) ? $alamat : '-',
                        'kelurahan_id' => $finalKelId,
                    ]);

                    Siswa::create([
                        'user_id' => $userSiswa->id,
                        'nis' => $nis,
                        'nisn' => $nisn,
                        'nama' => $nama,
                        'jenis_kelamin' => in_array($jk, ['l', 'p']) ? $jk : 'l',
                        'tempat_lahir_kabupaten_id' => $finalKabId,
                        'tanggal_lahir' => $tglLahir,
                        'agama' => $agama,
                        'pendidikan_sebelumnya' => $pendidikanPrev,
                        'alamat' => $alamat,
                        'orang_tua_id' => $orangTua->orang_tua_id,
                        'kelurahan_id' => $finalKelId,
                    ]);

                    $newCount++;
                }
            }

            DB::commit();
            session()->forget('master_siswa_import_data');

            $msg = "Proses impor selesai! Total $newCount siswa baru berhasil ditambahkan";
            if ($updateCount > 0) {
                $msg .= " dan $updateCount data siswa berhasil diperbarui.";
            } else {
                $msg .= ".";
            }

            return redirect()->route('akademik.master-siswa.index')->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('akademik.master-siswa.index')
                ->with('error', 'Gagal memproses simpan data impor: ' . $e->getMessage());
        }
    }

    /**
     * Smart Date Parser untuk mengonversi berbagai format tanggal Excel menjadi YYYY-MM-DD.
     */
    private function parseExcelDate($cell, $rawVal): string
    {
        if (empty($rawVal)) {
            return date('Y-m-d');
        }

        // 1. Cek jika cell merupakan Excel Serial Date Number atau formatted DateTime oleh PhpSpreadsheet
        if ($cell && ExcelDate::isDateTime($cell)) {
            try {
                return ExcelDate::excelToDateTimeObject($rawVal)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Lanjut ke pemeriksaan alternatif
            }
        }

        // 2. Cek jika nilai berupa angka murni (Excel Date Serial Number)
        if (is_numeric($rawVal) && (float)$rawVal > 1000) {
            try {
                return ExcelDate::excelToDateTimeObject((float)$rawVal)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Lanjut ke alternatif
            }
        }

        $val = trim((string) $rawVal);

        // 3. Format YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return $val;
        }

        // 4. Format DD/MM/YYYY atau DD-MM-YYYY (misal: 21/05/2008 atau 21-05-2008)
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $val, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "{$year}-{$month}-{$day}";
        }

        // 5. Format YYYY/MM/DD atau YYYY.MM.DD
        if (preg_match('/^(\d{4})[\/\.](\d{1,2})[\/\.](\d{1,2})$/', $val, $matches)) {
            $year = $matches[1];
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
            return "{$year}-{$month}-{$day}";
        }

        // 6. Percobaan fleksibel menggunakan Carbon / strtotime
        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Throwable $e) {
            return date('Y-m-d');
        }
    }
}

