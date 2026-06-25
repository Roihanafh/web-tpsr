<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Siswa;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TpsrKelasExport
{
    /**
     * Mapping Resume offset (0–9) → Input level index (0–4 = L0–L4).
     * Sesuai formula template asli:
     *   0 Disiplin        → L0 (offset 0)
     *   1 Sportivitas     → L0 (offset 0)
     *   2 Tanggung Jawab  → L1 (offset 1)
     *   3 Percaya Diri    → L1 (offset 1)
     *   4 Kemandirian     → L2 (offset 2)
     *   5 Berpikir Kritis → L2 (offset 2)
     *   6 Kepedulian      → L3 (offset 3)
     *   7 Kerja Sama      → L3 (offset 3)
     *   8 Kepemimpinan    → L3 (offset 3)
     *   9 Gaya Hidup Sehat→ L4 (offset 4)
     */
    private const RESUME_INPUT_OFFSET = [0, 0, 1, 1, 2, 2, 3, 3, 3, 4];

    // Input_TPSR: P16 ends at col 81 (CC).
    // Col 82 = Keterangan, col 83 = Rekomendasi
    private const COL_KETERANGAN  = 82;
    private const COL_REKOMENDASI = 83;

    public function export(Kelas $kelas): string
    {
        $templatePath = public_path('TPSR_template.xlsx');
        $spreadsheet  = IOFactory::load($templatePath);

        $siswaList = Siswa::where('kelas_id', $kelas->id)->orderBy('nama')->get();

        $allPenilaian = Penilaian::whereIn('siswa_id', $siswaList->pluck('id'))
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->keyBy('pertemuan'));

        $this->fillInputTpsr($spreadsheet, $kelas, $siswaList, $allPenilaian);
        $this->fillResumeKarakter($spreadsheet, $siswaList);
        $this->fillDashboardKarakter($spreadsheet, $siswaList);

        $tmpPath = tempnam(sys_get_temp_dir(), 'tpsr_') . '.xlsx';
        $writer  = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return $tmpPath;
    }

    // -------------------------------------------------------------------------
    // Tab Input_TPSR
    // Data nilai L0-L4 per pertemuan + kolom Keterangan & Rekomendasi di akhir.
    // -------------------------------------------------------------------------
    private function fillInputTpsr(
        Spreadsheet $spreadsheet,
        Kelas $kelas,
        $siswaList,
        $allPenilaian
    ): void {
        $sheet = $this->getSheet($spreadsheet, 'input_tpsr')
               ?? $spreadsheet->getActiveSheet();

        // Nama kelas di B2
        $sheet->getCellByColumnAndRow(2, 2)->setValue($kelas->nama);

        // Header kolom tambahan di baris 3 (row header level)
        $sheet->getCellByColumnAndRow(self::COL_KETERANGAN,  3)->setValue('Keterangan');
        $sheet->getCellByColumnAndRow(self::COL_REKOMENDASI, 3)->setValue('Rekomendasi');

        // Hapus data lama mulai baris 5
        $highestRow = $sheet->getHighestRow();
        for ($r = 5; $r <= $highestRow; $r++) {
            for ($c = 1; $c <= self::COL_REKOMENDASI; $c++) {
                $sheet->getCellByColumnAndRow($c, $r)->setValue(null);
            }
        }

        foreach ($siswaList as $idx => $siswa) {
            $row            = 5 + $idx;
            $penilaianSiswa = $allPenilaian->get($siswa->id, collect());

            // Kolom A: nama siswa
            $sheet->getCellByColumnAndRow(1, $row)->setValue($siswa->nama);

            // 16 pertemuan × 5 level (L0–L4)
            // Pertemuan n: startCol = 2 + (n-1)*5
            for ($p = 1; $p <= 16; $p++) {
                $startCol  = 2 + ($p - 1) * 5;
                $penilaian = $penilaianSiswa->get((string) $p);

                foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $li => $level) {
                    $val = $penilaian ? $penilaian->{$level} : null;
                    $sheet->getCellByColumnAndRow($startCol + $li, $row)->setValue($val);
                }
            }

            // Kolom Keterangan & Rekomendasi
            $sheet->getCellByColumnAndRow(self::COL_KETERANGAN,  $row)->setValue($siswa->keterangan  ?? '');
            $sheet->getCellByColumnAndRow(self::COL_REKOMENDASI, $row)->setValue($siswa->rekomendasi ?? '');
        }
    }

    // -------------------------------------------------------------------------
    // Tab Resume_Karakter
    // Menggunakan formula =Input_TPSR!{col}{row} persis seperti template asli.
    // -------------------------------------------------------------------------
    private function fillResumeKarakter(
        Spreadsheet $spreadsheet,
        $siswaList
    ): void {
        $sheet = $this->getSheet($spreadsheet, 'resume_karakter');
        if (! $sheet) return;

        // Hapus data lama baris 3+
        $highestRow = $sheet->getHighestRow();
        for ($r = 3; $r <= $highestRow; $r++) {
            for ($c = 1; $c <= 161; $c++) {
                $sheet->getCellByColumnAndRow($c, $r)->setValue(null);
            }
        }

        foreach ($siswaList as $idx => $siswa) {
            $resumeRow = 3 + $idx;
            $inputRow  = 5 + $idx; // baris di Input_TPSR

            // Kolom A: nama siswa (value langsung)
            $sheet->getCellByColumnAndRow(1, $resumeRow)->setValue($siswa->nama);

            // 16 pertemuan × 10 karakter
            // Resume pertemuan p: startCol = 2 + (p-1)*10
            // Input pertemuan p:  startCol = 2 + (p-1)*5
            for ($p = 1; $p <= 16; $p++) {
                $resumeStart = 2 + ($p - 1) * 10;
                $inputStart  = 2 + ($p - 1) * 5;

                for ($k = 0; $k < 10; $k++) {
                    $inputCol    = $inputStart + self::RESUME_INPUT_OFFSET[$k];
                    $inputColLtr = Coordinate::stringFromColumnIndex($inputCol);
                    $formula     = "=Input_TPSR!{$inputColLtr}{$inputRow}";
                    $sheet->getCellByColumnAndRow($resumeStart + $k, $resumeRow)
                          ->setValue($formula);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Tab Dashboard_Karakter
    // Formula AVERAGE dari 16 pertemuan di Resume_Karakter, sama seperti template.
    // -------------------------------------------------------------------------
    private function fillDashboardKarakter(
        Spreadsheet $spreadsheet,
        $siswaList
    ): void {
        $sheet = $this->getSheet($spreadsheet, 'dashboard_karakter');
        if (! $sheet) return;

        // Hapus data lama baris 3+
        $highestRow = $sheet->getHighestRow();
        for ($r = 3; $r <= $highestRow; $r++) {
            for ($c = 1; $c <= 11; $c++) {
                $sheet->getCellByColumnAndRow($c, $r)->setValue(null);
            }
        }

        foreach ($siswaList as $idx => $siswa) {
            $dashRow   = 3 + $idx;
            $resumeRow = 3 + $idx;

            // Kolom A: nama siswa
            $sheet->getCellByColumnAndRow(1, $dashRow)->setValue($siswa->nama);

            // 10 karakter (kolom B–K)
            // Karakter k (0-based): Resume kolom untuk pertemuan p = 2 + k + (p-1)*10
            for ($k = 0; $k < 10; $k++) {
                $refs = [];
                for ($p = 1; $p <= 16; $p++) {
                    $resumeCol    = 2 + $k + ($p - 1) * 10;
                    $resumeColLtr = Coordinate::stringFromColumnIndex($resumeCol);
                    $refs[]       = "Resume_Karakter!{$resumeColLtr}{$resumeRow}";
                }
                $formula = '=IFERROR(AVERAGE(' . implode(',', $refs) . '),"")';
                $sheet->getCellByColumnAndRow(2 + $k, $dashRow)->setValue($formula);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Helper: cari sheet by name (case-insensitive)
    // -------------------------------------------------------------------------
    private function getSheet(Spreadsheet $spreadsheet, string $nameLower): ?\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strtolower(trim($name)) === $nameLower) {
                return $spreadsheet->getSheetByName($name);
            }
        }
        return null;
    }
}
