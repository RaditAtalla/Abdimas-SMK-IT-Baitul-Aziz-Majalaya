<?php

namespace App\Http\Controllers\Intrakurikuler;

use App\Http\Controllers\Controller;
use App\Models\AsesmenSumatif;
use App\Models\Intrakurikuler;
use App\Models\LingkupMateri;
use App\Models\SkorAsesmenSiswa;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelController extends Controller
{
    // Generate template excel untuk asessment formatif
    public function downloadFormatif(Request $request, $intrakurikuler_id)
    {
        $intrakurikuler = Intrakurikuler::with([
            'tujuanPembelajaran',
            'kelasAjar.riwayatKelas.siswa.user',
            'kelasAjar.riwayatKelas.asesmenFormatif.details'
        ])->findOrFail($intrakurikuler_id);

        $tpList = $intrakurikuler->tujuanPembelajaran;
        $tpCount = $tpList->count();

        // Ambil semua siswa di kelas ajar
        $siswaList = $intrakurikuler->kelasAjar->riwayatKelas->map(function ($rk) {
            return $rk->siswa;
        })->unique('siswa_id')->values();

        // Map: riwayat_kelas_id => asesmen_formatif (beserta details)
        $formatifMap = [];
        foreach ($intrakurikuler->kelasAjar->riwayatKelas as $rk) {
            $formatif = $rk->asesmenFormatif
                ->where('intrakurikuler_id', $intrakurikuler_id)
                ->first();
            if ($formatif) {
                $details = $formatif->details->keyBy('tujuan_pembelajaran_id');
                $formatifMap[$rk->riwayat_kelas_id] = [
                    'formatif' => $formatif,
                    'details' => $details,
                ];
            }
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $startTPColIndex = 3; // C = 3 (1-based)
        $lastTPColIndex  = $startTPColIndex + ($tpCount * 2) - 1;

        $startTPCol = Coordinate::stringFromColumnIndex($startTPColIndex); // "C"
        $lastTPCol  = Coordinate::stringFromColumnIndex($lastTPColIndex);

        $colTertinggi = Coordinate::stringFromColumnIndex($lastTPColIndex + 1);
        $colTerendah  = Coordinate::stringFromColumnIndex($lastTPColIndex + 2);

        // Merge header
        $sheet->mergeCells('A1:A3');
        $sheet->setCellValue('A1', 'No');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('B1:B3');
        $sheet->setCellValue('B1', 'Nama Siswa');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("C1:{$lastTPCol}1");
        $sheet->setCellValue('C1', 'Tujuan Pembelajaran');
        $sheet->getStyle("C1:{$lastTPCol}1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->mergeCells($colTertinggi . '1:' . $colTertinggi . '3');
        $sheet->setCellValue($colTertinggi . '1', 'Deskripsi Capaian Tertinggi dalam Rapor');
        $sheet->getStyle($colTertinggi . '1')->getAlignment()->setWrapText(true)->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension($colTertinggi)->setWidth(28);

        $sheet->mergeCells($colTerendah . '1:' . $colTerendah . '3');
        $sheet->setCellValue($colTerendah . '1', 'Deskripsi Capaian Terendah dalam Rapor');
        $sheet->getStyle($colTerendah . '1')->getAlignment()->setWrapText(true)->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension($colTerendah)->setWidth(28);

        // Header baris 2: TP, KKTP, Tampil/Tidak
        $sheet->setCellValue('A2', '');
        $sheet->setCellValue('B2', '');
        for ($i = 0; $i < $tpCount; $i++) {
            $colKKTP  = Coordinate::stringFromColumnIndex($startTPColIndex + ($i * 2));
            $colTampil = Coordinate::stringFromColumnIndex($startTPColIndex + ($i * 2) + 1);
            $sheet->setCellValue($colKKTP . '2', 'TP ' . ($i + 1) . ' KKTP');
            $sheet->setCellValue($colTampil . '2', 'TP ' . ($i + 1) . ' Tampil/Tidak');
            $sheet->getColumnDimension($colTampil)->setWidth(15);
        }

        // Header baris 3: Deskripsi TP (wrap text)
        $sheet->setCellValue('A3', '');
        $sheet->setCellValue('B3', '');
        for ($i = 0; $i < $tpCount; $i++) {
            $colKKTP  = Coordinate::stringFromColumnIndex($startTPColIndex + ($i * 2));
            $colTampil = Coordinate::stringFromColumnIndex($startTPColIndex + ($i * 2) + 1);
            $desc = $tpList[$i]->deskripsi;
            $sheet->setCellValue($colKKTP . '3', $desc);
            $sheet->mergeCells($colKKTP . '3:' . $colTampil . '3');
            $sheet->getStyle($colKKTP . '3:' . $colTampil . '3')
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Data siswa
        $row = 4;
        foreach ($intrakurikuler->kelasAjar->riwayatKelas as $rk) {
            $siswa = $rk->siswa;
            $namaSiswa = $siswa->user->name ?? $siswa->nama ?? '-';

            $sheet->setCellValue('A' . $row, $row - 3);
            $sheet->setCellValue('B' . $row, $namaSiswa);

            // Ambil data formatif & details jika ada
            $formatif = $formatifMap[$rk->riwayat_kelas_id]['formatif'] ?? null;
            $details = $formatifMap[$rk->riwayat_kelas_id]['details'] ?? collect();

            // Kolom KKTP & Tampil/Tidak, isi default dari database jika ada
            for ($i = 0; $i < $tpCount; $i++) {
                $tp = $tpList[$i];
                $colKKTP  = Coordinate::stringFromColumnIndex($startTPColIndex + ($i * 2));
                $colTampil = Coordinate::stringFromColumnIndex($startTPColIndex + ($i * 2) + 1);

                $kktpVal = '';
                $tampilVal = '';
                if ($details && $details->has($tp->tujuan_pembelajaran_id)) {
                    $detail = $details->get($tp->tujuan_pembelajaran_id);
                    $kktpVal = $detail->kktp ? 1 : 0;
                    $tampilVal = $detail->tampil ? 1 : 0;
                }

                $sheet->setCellValue($colKKTP . $row, $kktpVal);
                $sheet->setCellValue($colTampil . $row, $tampilVal);

                // Validasi KKTP
                $validationKKTP = $sheet->getCell($colKKTP . $row)->getDataValidation();
                $validationKKTP->setType(DataValidation::TYPE_LIST);
                $validationKKTP->setErrorStyle(DataValidation::STYLE_STOP);
                $validationKKTP->setAllowBlank(true);
                $validationKKTP->setShowInputMessage(true);
                $validationKKTP->setShowErrorMessage(true);
                $validationKKTP->setShowDropDown(true);
                $validationKKTP->setFormula1('"0,1"');
                $validationKKTP->setErrorTitle('Input Salah');
                $validationKKTP->setError('Hanya boleh 0 atau 1');
                $validationKKTP->setPromptTitle('Input');
                $validationKKTP->setPrompt('Masukkan 1 jika KKTP Tercapai, 0 sebaliknya');

                // Validasi Tampil/Tidak
                $validationTampil = $sheet->getCell($colTampil . $row)->getDataValidation();
                $validationTampil->setType(DataValidation::TYPE_LIST);
                $validationTampil->setErrorStyle(DataValidation::STYLE_STOP);
                $validationTampil->setAllowBlank(true);
                $validationTampil->setShowInputMessage(true);
                $validationTampil->setShowErrorMessage(true);
                $validationTampil->setShowDropDown(true);
                $validationTampil->setFormula1('"0,1"');
                $validationTampil->setErrorTitle('Input Salah');
                $validationTampil->setError('Hanya boleh 0 atau 1');
                $validationTampil->setPromptTitle('Input');
                $validationTampil->setPrompt('Masukkan 0 jika tidak ingin ditampilkan di deskripsi capaian, 1 sebaliknya');
            }

            // Buat array untuk referensi deskripsi TP dan kolom Excel
            $tpDeskripsi = [];
            $tpKKTPCols = [];
            $tpTampilCols = [];
            for ($i = 0; $i < $tpCount; $i++) {
                $colKKTP  = Coordinate::stringFromColumnIndex($startTPColIndex + ($i * 2));
                $colTampil = Coordinate::stringFromColumnIndex($startTPColIndex + ($i * 2) + 1);
                $tpDeskripsi[] = $tpList[$i]->deskripsi;
                $tpKKTPCols[] = $colKKTP . $row;
                $tpTampilCols[] = $colTampil . $row;
            }

            // Rumus Excel untuk capaian tertinggi: gabungkan deskripsi TP yang KKTP=1 dan Tampil=1
            $formulaTertinggi = '';
            for ($i = 0; $i < $tpCount; $i++) {
                $formulaTertinggi .= 'IF(AND(' . $tpKKTPCols[$i] . '=1,' . $tpTampilCols[$i] . '=1),"' . str_replace('"', '""', strtolower($tpDeskripsi[$i])) . ', ","")';
                if ($i < $tpCount - 1) $formulaTertinggi .= '&';
            }
            // Hilangkan koma terakhir jika ada
            $formulaTertinggi = '="' . $namaSiswa . ' menunjukkan pemahaman dalam " &' . $formulaTertinggi;

            // Rumus Excel untuk capaian terendah: gabungkan deskripsi TP yang KKTP=0 dan Tampil=1
            $formulaTerendah = '';
            for ($i = 0; $i < $tpCount; $i++) {
                $formulaTerendah .= 'IF(AND(' . $tpKKTPCols[$i] . '=0,' . $tpTampilCols[$i] . '=1),"' . str_replace('"', '""', strtolower($tpDeskripsi[$i])) . ', ","")';
                if ($i < $tpCount - 1) $formulaTerendah .= '&';
            }
            $formulaTerendah = '="' . $namaSiswa . ' membutuhkan bimbingan dalam " &' . $formulaTerendah;

            // Set formula ke cell
            $sheet->setCellValueExplicit($colTertinggi . $row, $formulaTertinggi, DataType::TYPE_FORMULA);
            $sheet->setCellValueExplicit($colTerendah . $row, $formulaTerendah, DataType::TYPE_FORMULA);

            $sheet->getStyle($colTertinggi)->getAlignment()->setWrapText(true);
            $sheet->getStyle($colTerendah)->getAlignment()->setWrapText(true);

            // Set alignment center untuk seluruh kolom dari A sampai L pada baris siswa
            $sheet->getStyle("A{$row}:{$colTerendah}{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $row++;
        }

        // Auto size kolom Nama Siswa (B)
        $sheet->getColumnDimension('B')->setAutoSize(true);

        $namaKelas = $intrakurikuler->kelasAjar->kelas->nama_kelas;
        $tahunAjaran = str_replace('/', '-', $intrakurikuler->kelasAjar->tahunAjaran->tahun);
        $semester = $intrakurikuler->kelasAjar->tahunAjaran->semester;
        $namaIntrakurikuler = $intrakurikuler->nama_pelajaran;

        // Download
        $filename = 'Template Asesmen Formatif ' . "$namaIntrakurikuler $namaKelas $tahunAjaran $semester" . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // Generate template excel untuk asessment sumatif
    public function downloadSumatifTemplate(Request $request, $intrakurikuler_id)
    {
        $intrakurikuler = Intrakurikuler::with([
            'kelasAjar.kelas',
            'kelasAjar.tahunAjaran',
            'kelasAjar.riwayatKelas.siswa.user',
        ])->findOrFail($intrakurikuler_id);

        $tahunAjaranId = $intrakurikuler->kelasAjar?->tahun_ajaran_id;

        // Lingkup materi untuk kolom Sumatif 1..n
        $lingkupList = LingkupMateri::where('intrakurikuler_id', $intrakurikuler_id)
            ->orderBy('lingkup_materi_id')
            ->get();

        // fallback: minimal 10 kolom sumatif
        if ($lingkupList->count() === 0) {
            $lingkupList = collect(range(1, 10))->map(fn() => (object)[
                'lingkup_materi_id' => null,
                'nama_materi' => '',
            ]);
        }

        $sumatifCount = $lingkupList->count();

        // Siswa dari riwayat kelas
        $riwayatKelasList = $intrakurikuler->kelasAjar?->riwayatKelas ?? collect();
        $riwayatIds = $riwayatKelasList->pluck('riwayat_kelas_id')->filter()->values();

        // 1) Ambil asesmen sumatif existing
        $asesmen = AsesmenSumatif::query()
            ->where('intrakurikuler_id', $intrakurikuler_id)
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->get();

        $lingkupAsesmenMap = $asesmen
            ->where('asesmen_type', 'sumatif_lingkup')
            ->filter(fn($a) => !is_null($a->lingkup_materi_id))
            ->keyBy('lingkup_materi_id');

        $asesmenTestId    = optional($asesmen->firstWhere('asesmen_type', 'test'))->asesmen_sumatif_id;
        $asesmenNonTestId = optional($asesmen->firstWhere('asesmen_type', 'non_test'))->asesmen_sumatif_id;

        $asesmenIds = $asesmen->pluck('asesmen_sumatif_id')->filter()->values();

        // 2) Ambil skor existing
        $skorMap = collect();
        if ($riwayatIds->isNotEmpty() && $asesmenIds->isNotEmpty()) {
            $skorMap = SkorAsesmenSiswa::query()
                ->whereIn('riwayat_kelas_id', $riwayatIds)
                ->whereIn('asesmen_sumatif_id', $asesmenIds)
                ->get()
                ->keyBy(fn($s) => $s->riwayat_kelas_id . ':' . $s->asesmen_sumatif_id);
        }

        // ===========================
        // EXCEL
        // ===========================
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Sumatif');

        // Kolom dinamis
        $colNoIndex   = 1; // A
        $colNamaIndex = 2; // B

        $firstSumatifColIndex = 3; // C
        $lastSumatifColIndex  = $firstSumatifColIndex + $sumatifCount - 1;

        $colNaLingkupIndex  = $lastSumatifColIndex + 1;
        $colNonTesIndex     = $colNaLingkupIndex + 1;
        $colTesIndex        = $colNaLingkupIndex + 2;
        $colNaAkhirSemIndex = $colNaLingkupIndex + 3;
        $colNilaiRaporIndex = $colNaLingkupIndex + 4;

        $colNo   = Coordinate::stringFromColumnIndex($colNoIndex);
        $colNama = Coordinate::stringFromColumnIndex($colNamaIndex);

        $firstSumatifCol = Coordinate::stringFromColumnIndex($firstSumatifColIndex);
        $lastSumatifCol  = Coordinate::stringFromColumnIndex($lastSumatifColIndex);

        $colNaLingkup  = Coordinate::stringFromColumnIndex($colNaLingkupIndex);
        $colNonTes     = Coordinate::stringFromColumnIndex($colNonTesIndex);
        $colTes        = Coordinate::stringFromColumnIndex($colTesIndex);
        $colNaAkhirSem = Coordinate::stringFromColumnIndex($colNaAkhirSemIndex);
        $colNilaiRapor = Coordinate::stringFromColumnIndex($colNilaiRaporIndex);

        $lastCol = $colNilaiRapor;

        // ===== HEADER (3 baris) =====
        $sheet->mergeCells("{$colNo}1:{$colNo}3");
        $sheet->mergeCells("{$colNama}1:{$colNama}3");
        $sheet->setCellValue("{$colNo}1", "No");
        $sheet->setCellValue("{$colNama}1", "Nama Siswa");

        $sheet->mergeCells("{$firstSumatifCol}1:{$colNaLingkup}1");
        $sheet->setCellValue("{$firstSumatifCol}1", "Sumatif Akhir Lingkup Materi (Wajib)");

        $sheet->mergeCells("{$colNonTes}1:{$colNaAkhirSem}1");
        $sheet->setCellValue("{$colNonTes}1", "Sumatif Akhir Semester (Tidak Wajib)");

        $sheet->mergeCells("{$colNilaiRapor}1:{$colNilaiRapor}3");
        $sheet->setCellValue("{$colNilaiRapor}1", "Nilai Rapor");

        for ($i = 0; $i < $sumatifCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($firstSumatifColIndex + $i);
            $sheet->setCellValue("{$col}2", "Sumatif " . ($i + 1));
        }

        $sheet->setCellValue("{$colNaLingkup}2", "NA Sumatif\nLingkup Materi");
        $sheet->setCellValue("{$colNonTes}2", "Non Tes");
        $sheet->setCellValue("{$colTes}2", "Tes");
        $sheet->setCellValue("{$colNaAkhirSem}2", "NA Sumatif\nAkhir Semester");

        for ($i = 0; $i < $sumatifCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($firstSumatifColIndex + $i);
            $sheet->setCellValue("{$col}3", $lingkupList[$i]->nama_materi ?? '');
        }
        $sheet->setCellValue("{$colNaLingkup}3", "-");
        $sheet->setCellValue("{$colNonTes}3", "-");
        $sheet->setCellValue("{$colTes}3", "-");
        $sheet->setCellValue("{$colNaAkhirSem}3", "-");

        // ===== DATA SISWA mulai baris 4 =====
        $row = 4;
        $no = 1;

        foreach ($riwayatKelasList as $rk) {
            $riwayatId = $rk->riwayat_kelas_id;
            $siswa = $rk->siswa;
            $namaSiswa = $siswa?->user?->name ?? $siswa?->nama ?? '-';

            $sheet->setCellValue("{$colNo}{$row}", $no++);
            $sheet->setCellValue("{$colNama}{$row}", $namaSiswa);

            // SUMATIF LINGKUP
            for ($i = 0; $i < $sumatifCount; $i++) {
                $col = Coordinate::stringFromColumnIndex($firstSumatifColIndex + $i);

                $lingkupId = $lingkupList[$i]->lingkup_materi_id ?? null;
                $nilai = null;

                if ($lingkupId && $lingkupAsesmenMap->has($lingkupId)) {
                    $asesmenId = $lingkupAsesmenMap[$lingkupId]->asesmen_sumatif_id;
                    $key = $riwayatId . ':' . $asesmenId;
                    $nilai = $skorMap->has($key) ? $skorMap[$key]->nilai : null;
                }

                if ($nilai === null || $nilai === '') {
                    $sheet->setCellValueExplicit("{$col}{$row}", "", DataType::TYPE_STRING);
                } else {
                    // pastikan bener-bener angka (ini sering bikin NA/formula jadi aman)
                    $sheet->setCellValueExplicit("{$col}{$row}", (float)$nilai, DataType::TYPE_NUMERIC);
                }
            }

            // NON TEST & TEST
            $nonTesVal = null;
            if ($asesmenNonTestId) {
                $key = $riwayatId . ':' . $asesmenNonTestId;
                $nonTesVal = $skorMap->has($key) ? $skorMap[$key]->nilai : null;
            }

            $tesVal = null;
            if ($asesmenTestId) {
                $key = $riwayatId . ':' . $asesmenTestId;
                $tesVal = $skorMap->has($key) ? $skorMap[$key]->nilai : null;
            }

            $sheet->setCellValueExplicit("{$colNonTes}{$row}", ($nonTesVal === null || $nonTesVal === '') ? "" : (float)$nonTesVal, ($nonTesVal === null || $nonTesVal === '') ? DataType::TYPE_STRING : DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("{$colTes}{$row}", ($tesVal === null || $tesVal === '') ? "" : (float)$tesVal, ($tesVal === null || $tesVal === '') ? DataType::TYPE_STRING : DataType::TYPE_NUMERIC);

            // Formula NA Lingkup Materi
            $rangeSumatif = "{$firstSumatifCol}{$row}:{$lastSumatifCol}{$row}";
            $sheet->setCellValue(
                "{$colNaLingkup}{$row}",
                "=IFERROR(IF(COUNT({$rangeSumatif})=0,\"-\",ROUND(AVERAGE({$rangeSumatif}),0)),\"-\")"
            );

            // Formula NA Akhir Semester
            $rangeAkhirSem = "{$colNonTes}{$row}:{$colTes}{$row}";
            $sheet->setCellValue(
                "{$colNaAkhirSem}{$row}",
                "=IFERROR(IF(COUNT({$rangeAkhirSem})=0,\"\",ROUND(AVERAGE({$rangeAkhirSem}),0)),\"\")"
            );

            // Nilai rapor
            // $sheet->setCellValue(
            //     "{$colNilaiRapor}{$row}",
            //     "=IF({$colNaAkhirSem}{$row}<>\"\",{$colNaAkhirSem}{$row},{$colNaLingkup}{$row})"
            // );

            $sheet->setCellValue(
                "{$colNilaiRapor}{$row}",
                "=IF(AND({$colNaAkhirSem}{$row}<>\"\",{$colNaLingkup}{$row}<>\"-\"),"
                    . "ROUND(AVERAGE({$colNaAkhirSem}{$row},{$colNaLingkup}{$row}),0),"
                    . "IF({$colNaAkhirSem}{$row}<>\"\",{$colNaAkhirSem}{$row},IF({$colNaLingkup}{$row}=\"-\",\"\",{$colNaLingkup}{$row})))"
            );


            $row++;
        }

        $lastRow = $row - 1;

        // ===========================
        // STYLING (biar rapi & ga “bug”)
        // ===========================
        // ukuran kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(32);

        for ($i = 0; $i < $sumatifCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($firstSumatifColIndex + $i);
            $sheet->getColumnDimension($col)->setWidth(12);
        }

        $sheet->getColumnDimension($colNaLingkup)->setWidth(16);
        $sheet->getColumnDimension($colNonTes)->setWidth(10);
        $sheet->getColumnDimension($colTes)->setWidth(10);
        $sheet->getColumnDimension($colNaAkhirSem)->setWidth(16);
        $sheet->getColumnDimension($colNilaiRapor)->setWidth(12);

        // tinggi header biar wrap aman
        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(34);
        $sheet->getRowDimension(3)->setRowHeight(42);

        // wrap + center header
        $sheet->getStyle("A1:{$lastCol}3")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle("A1:{$lastCol}3")->getFont()->setBold(true);

        // background header
        $sheet->getStyle("A1:{$lastCol}3")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEFEFEF');

        // border seluruh table
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // format angka (0) untuk nilai input & hasil rapor
        $inputStartCol = $firstSumatifCol;
        $inputEndCol = $colTes; // sampai tes
        $sheet->getStyle("{$inputStartCol}4:{$inputEndCol}{$lastRow}")
            ->getNumberFormat()->setFormatCode('0');

        $sheet->getStyle("{$colNilaiRapor}4:{$colNilaiRapor}{$lastRow}")
            ->getNumberFormat()->setFormatCode('0');

        // Freeze: biar No & Nama tetap terlihat
        $sheet->freezePane("{$firstSumatifCol}4");

        // print setup (biar enak diprint)
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);
        $sheet->setAutoFilter("A3:{$lastCol}3");

        // Filename aman
        $namaKelas = $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '';
        $tahunAjar = $intrakurikuler->kelasAjar?->tahunAjaran?->tahun ?? '';
        $semester  = $intrakurikuler->kelasAjar?->tahunAjaran?->semester ?? '';

        $base = "template_asesmen_sumatif_{$intrakurikuler->nama_pelajaran}_{$namaKelas}_{$tahunAjar}_{$semester}";
        $base = preg_replace('/[\/\\\\\?\%\*\:\|\"<>\r\n]+/', '_', $base);
        $base = trim($base, " ._");
        $filename = $base . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function downloadInputTpLmTemplate(Request $request, $intrakurikuler_id)
    {
        $intrakurikuler = Intrakurikuler::with([
            'kelasAjar.kelas',
            'kelasAjar.tahunAjaran',
        ])->findOrFail($intrakurikuler_id);

        // ===========================
        // EXCEL
        // ===========================
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template TP dan LM');

        // ===== HEADER (Baris 1) =====
        $sheet->setCellValue('A1', 'Lingkup Materi');
        $sheet->setCellValue('B1', 'Tujuan Pembelajaran');

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getStyle('A1:B1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:B1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEFEFEF');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(55);

        // Filename
        $namaKelas = $intrakurikuler->kelasAjar?->kelas?->nama_kelas ?? '';
        $tahunAjar = $intrakurikuler->kelasAjar?->tahunAjaran?->tahun ?? '';
        $semester  = $intrakurikuler->kelasAjar?->tahunAjaran?->semester ?? '';

        $base = "template_tp_lm_{$intrakurikuler->nama_pelajaran}_{$namaKelas}_{$tahunAjar}_{$semester}";
        $base = preg_replace('/[\/\\\\\?\%\*\:\|\"<>\r\n]+/', '_', $base);
        $base = trim($base, " ._");
        $filename = $base . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function importInputTpLm(Request $request, $intrakurikuler_id)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
            'mode'  => 'nullable|in:tambah,timpa',
        ]);

        $intrakurikuler = Intrakurikuler::findOrFail($intrakurikuler_id);

        $user = auth()->user();
        if (!$user->hasRole('Bagian Akademik') && !$user->hasRole('Super Admin') && $intrakurikuler->pengampu_user_id != $user->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengimport data pada intrakurikuler ini.');
        }

        $file = $request->file('excel');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel. Pastikan format file benar.');
        }

        DB::beginTransaction();
        try {
            if ($request->input('mode') === 'timpa') {
                LingkupMateri::where('intrakurikuler_id', $intrakurikuler_id)->delete();
                TujuanPembelajaran::where('intrakurikuler_id', $intrakurikuler_id)->delete();
            }
            $lmCount = 0;
            $tpCount = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $namaMateri = trim((string) $sheet->getCell("A{$row}")->getValue());
                $deskripsi  = trim((string) $sheet->getCell("B{$row}")->getValue());

                if ($namaMateri !== '') {
                    $lm = LingkupMateri::firstOrCreate([
                        'intrakurikuler_id' => $intrakurikuler_id,
                        'nama_materi' => $namaMateri,
                    ]);
                    if ($lm->wasRecentlyCreated) {
                        $lmCount++;
                    }
                }

                if ($deskripsi !== '') {
                    $tp = TujuanPembelajaran::firstOrCreate([
                        'intrakurikuler_id' => $intrakurikuler_id,
                        'deskripsi' => $deskripsi,
                    ]);
                    if ($tp->wasRecentlyCreated) {
                        $tpCount++;
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', "Data Excel berhasil diimport ($lmCount Lingkup Materi baru, $tpCount Tujuan Pembelajaran baru).");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengimport data Excel: ' . $th->getMessage());
        }
    }
}
