<?php

namespace App\Http\Controllers\Ekstrakurikuler;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\PenilaianEkstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EkstrakurikulerExcelController extends Controller
{
    /**
     * Download template Excel untuk input penilaian ekstrakurikuler.
     */
    public function downloadTemplate(Request $request, $ekstrakurikuler_id)
    {
        $ekskul = Ekstrakurikuler::with([
            'peserta.siswa.user',
            'peserta.penilaians',
            'tahunAjaran'
        ])->findOrFail($ekstrakurikuler_id);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penilaian Ekstrakurikuler');

        // Header Informasi
        $sheet->setCellValue('A1', 'Mata Pelajaran Ekstrakurikuler');
        $sheet->setCellValue('B1', ': ' . $ekskul->nama_pelajaran);
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->setCellValue('A2', 'Tahun Ajaran / Semester');
        $tahun = $ekskul->tahunAjaran->tahun ?? '-';
        $semester = $ekskul->tahunAjaran->semester ?? '-';
        $sheet->setCellValue('B2', ": $tahun ($semester)");
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Header Table di Baris 4
        $headers = ['ID Peserta (Jangan Diubah)', 'No', 'NISN', 'Nama Siswa', 'Deskripsi Penilaian'];
        $cols = ['A', 'B', 'C', 'D', 'E'];

        foreach ($headers as $index => $header) {
            $col = $cols[$index];
            $sheet->setCellValue("{$col}4", $header);
        }

        // Format Header Table
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);
        $sheet->getStyle('A4:E4')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A4:E4')->getFill()->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle('A4:E4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(4)->setRowHeight(26);

        // Data Peserta mulai baris 5
        $row = 5;
        $no = 1;
        foreach ($ekskul->peserta as $peserta) {
            $siswa = $peserta->siswa;
            $namaSiswa = $siswa->user->name ?? $siswa->nama ?? '-';
            $nisn = $siswa->nisn ?? '-';
            $penilaian = $peserta->penilaians->first();
            $deskripsi = $penilaian ? $penilaian->deskripsi : '';

            $sheet->setCellValue("A{$row}", $peserta->siswa_ekstrakurikuler_id);
            $sheet->setCellValue("B{$row}", $no++);
            $sheet->setCellValueExplicit("C{$row}", $nisn, DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $namaSiswa);
            $sheet->setCellValue("E{$row}", $deskripsi);

            $sheet->getStyle("A{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $row++;
        }

        $lastRow = $row - 1;

        // Auto width & Styling
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(8);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(50);

        if ($lastRow >= 4) {
            $sheet->getStyle("A4:E{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $namaEkstrakurikuler = str_replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'], '_', $ekskul->nama_pelajaran);
        $filename = "Template_Penilaian_Ekskul_{$namaEkstrakurikuler}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import hasil penilaian dari file Excel.
     */
    public function importExcel(Request $request, $ekstrakurikuler_id)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        $ekskul = Ekstrakurikuler::findOrFail($ekstrakurikuler_id);
        $file = $request->file('file_excel');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            $updatedCount = 0;
            DB::beginTransaction();

            // Membaca baris dari baris 5 (sesuai template)
            for ($row = 5; $row <= $highestRow; $row++) {
                $siswaEkskulId = trim($sheet->getCell("A{$row}")->getValue());
                $deskripsi = trim($sheet->getCell("E{$row}")->getValue());

                if (empty($siswaEkskulId)) {
                    continue;
                }

                // Simpan atau update penilaian jika ada deskripsi yang diisi
                if (!empty($deskripsi)) {
                    PenilaianEkstrakurikuler::updateOrCreate(
                        ['siswa_ekstrakurikuler_id' => $siswaEkskulId],
                        ['deskripsi' => $deskripsi]
                    );
                    $updatedCount++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success', "Berhasil mengimpor $updatedCount data penilaian ekstrakurikuler.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengimpor file Excel: ' . $e->getMessage());
        }
    }
}
