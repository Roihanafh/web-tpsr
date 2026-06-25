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
     * Mapping L0–L4 ke 10 kolom karakter per pertemuan di Resume_Karakter.
     * Urutan kolom (offset 0–9 dari startCol):
     *   0 Disiplin        = L0
     *   1 Sportivitas     = L0
     *   2 Tanggung Jawab  = L1
     *   3 Percaya Diri    = L1
     *   4 Kemandirian     = L2
     *   5 Berpikir Kritis = L2
     *   6 Kepedulian      = L3
     *   7 Kerja Sama      = L3
     *   8 Kepemimpinan    = L3
     *   9 Gaya Hidup Sehat = L4
     */
    private const RESUME_LEVEL_MAP = [
        0 => 'L0', // Disiplin
        1 => 'L0', // Sportivitas
        2 => 'L1', // Tanggung Jawab
        3 => 'L1', // Percaya Diri
        4 => 'L2', // Kemandirian
        5 => 'L2', // Berpikir Kritis
        6 => 'L3', // Kepedulian
        7 => 'L3', // Kerja Sama
        8 => 'L3', // Kepemimpinan
        9 => 'L4', // Gaya Hidup Sehat
    ];

    private const KARAKTER_NAMES = [
        'Disiplin', 'Sportivitas', 'Tanggung Jawab', 'Percaya Diri',
        'Kemandirian', 'Berpikir Kritis', 'Kepedulian',
        'Kerja Sama', 'Kepemimpinan', 'Gaya Hidup Sehat',
    ];

    public function export(Kelas $kelas): string
    {
        // Load template sebagai base agar styling/format terjaga
        $templatePath = public_path('TPSR_template.xlsx');
        $spreadsheet  = IOFactory::load($templatePath);

        $siswaList = Siswa::where('kelas_id', $kelas->id)->orderBy('nama')->get();

        // Ambil semua penilaian sekaligus
        $allPenilaian = Penilaian::whereIn('siswa_id', $siswaList->pluck('id'))
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->keyBy('pertemuan'));

        $this->fillInputTpsr($spreadsheet, $kelas, $siswaList, $allPenilaian);
        $this->fillResumeKarakter($spreadsheet, $siswaList, $allPenilaian);
        $this->fillDashboardKarakter($spreadsheet, $siswaList);

        // Simpan ke temp file
        $tmpPath = tempnam(sys_get_temp_dir(), 'tpsr_') . '.xlsx';
        $writer  = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return $tmpPath;
    }

    // -------------------------------------------------------------------------
    // Tab Input_TPSR
    // -------------------------------------------------------------------------
    private function fillInputTpsr(
        Spreadsheet $spreadsheet,
        Kelas $kelas,
        $siswaList,
        $allPenilaian
    ): void {
        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strtolower(trim($name)) === 'input_tpsr') {
                $sheet = $spreadsheet->getSheetByName($name);
                break;
            }
        }
        if (! $sheet) {
            $sheet = $spreadsheet->getActiveSheet();
        }

        // Nama kelas di B2
        $sheet->getCellByColumnAndRow(2, 2)->setValue($kelas->nama);

        // Hapus data contoh yang ada (baris 5+)
        $highestRow = $sheet->getHighestRow();
        for ($r = 5; $r <= $highestRow; $r++) {
            for ($c = 1; $c <= 81; $c++) {
                $sheet->getCellByColumnAndRow($c, $r)->setValue(null);
            }
        }

        // Tulis data siswa mulai baris 5
        foreach ($siswaList as $idx => $siswa) {
            $row     = 5 + $idx;
            $penilaianSiswa = $allPenilaian->get($siswa->id, collect());

            // Kolom A: nama siswa
            $sheet->getCellByColumnAndRow(1, $row)->setValue($siswa->nama);

            // 16 pertemuan: L0-L4 per pertemuan
            for ($p = 1; $p <= 16; $p++) {
                $startCol = 2 + ($p - 1) * 5; // P1=2(B), P2=7(G), ... P16=77(BW)
                $penilaian = $penilaianSiswa->get((string) $p);

                foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $li => $level) {
                    $val = $penilaian ? $penilaian->{$level} : null;
                    $sheet->getCellByColumnAndRow($startCol + $li, $row)
                          ->setValue($val);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Tab Resume_Karakter
    // -------------------------------------------------------------------------
    private function fillResumeKarakter(
        Spreadsheet $spreadsheet,
        $siswaList,
        $allPenilaian
    ): void {
        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strtolower(trim($name)) === 'resume_karakter') {
                $sheet = $spreadsheet->getSheetByName($name);
                break;
            }
        }
        if (! $sheet) return;

        // Hapus data baris lama (baris 3+)
        $highestRow = $sheet->getHighestRow();
        for ($r = 3; $r <= $highestRow; $r++) {
            for ($c = 1; $c <= 161; $c++) {
                $sheet->getCellByColumnAndRow($c, $r)->setValue(null);
            }
        }

        // Tulis data: baris 3 = siswa pertama, dst.
        foreach ($siswaList as $idx => $siswa) {
            $row = 3 + $idx;
            $penilaianSiswa = $allPenilaian->get($siswa->id, collect());

            // Kolom A: nama siswa
            $sheet->getCellByColumnAndRow(1, $row)->setValue($siswa->nama);

            // 16 pertemuan × 10 karakter
            for ($p = 1; $p <= 16; $p++) {
                $startCol  = 2 + ($p - 1) * 10; // P1=2(B), P2=12(L), ... P16=152(EV)
                $penilaian = $penilaianSiswa->get((string) $p);

                foreach (self::RESUME_LEVEL_MAP as $offset => $level) {
                    $val = $penilaian ? $penilaian->{$level} : null;
                    $sheet->getCellByColumnAndRow($startCol + $offset, $row)
                          ->setValue($val);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Tab Dashboard_Karakter — rata-rata 16 pertemuan per karakter
    // -------------------------------------------------------------------------
    private function fillDashboardKarakter(
        Spreadsheet $spreadsheet,
        $siswaList
    ): void {
        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strtolower(trim($name)) === 'dashboard_karakter') {
                $sheet = $spreadsheet->getSheetByName($name);
                break;
            }
        }
        if (! $sheet) return;

        // Hapus data baris lama (baris 3+)
        $highestRow = $sheet->getHighestRow();
        for ($r = 3; $r <= $highestRow; $r++) {
            for ($c = 1; $c <= 11; $c++) {
                $sheet->getCellByColumnAndRow($c, $r)->setValue(null);
            }
        }

        foreach ($siswaList as $idx => $siswa) {
            $row = 3 + $idx;

            // Kolom A: nama siswa
            $sheet->getCellByColumnAndRow(1, $row)->setValue($siswa->nama);

            // 10 kolom karakter (B–K): AVERAGE dari 16 pertemuan di Resume
            // Karakter ke-k (0-indexed) di Resume: kolom = 2 + k + (p-1)*10
            // Referensi Resume row = row (sama karena urutan siswa sama)
            for ($k = 0; $k < 10; $k++) {
                $resumeRefs = [];
                for ($p = 1; $p <= 16; $p++) {
                    $resumeCol = 2 + $k + ($p - 1) * 10;
                    $resumeColLetter = Coordinate::stringFromColumnIndex($resumeCol);
                    $resumeRefs[] = "Resume_Karakter!{$resumeColLetter}{$row}";
                }
                $formula = '=IFERROR(AVERAGE(' . implode(',', $resumeRefs) . '),"")';
                $sheet->getCellByColumnAndRow(2 + $k, $row)->setValue($formula);
            }
        }
    }
}
