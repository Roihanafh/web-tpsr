<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Sekolah;
use App\Models\Siswa;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SipenaPenilaianImport
{
    // Hasil import
    public array  $imported   = [];   // [{nama, pertemuan, action}]
    public array  $skipped    = [];   // [{nama, reason}]
    public array  $warnings   = [];   // pesan peringatan (non-fatal)
    public ?string $kelasNama  = null;
    public ?int    $pertemuan  = null;
    public bool   $sekolahMismatch = false;

    private Sekolah $sekolah;

    public function __construct(Sekolah $sekolah)
    {
        $this->sekolah = $sekolah;
    }

    /**
     * Membaca file XLS SIPENA KARAKTER dan mengimport data penilaian.
     *
     * Layout sel (1-based row, 1-based col):
     *  Row 3, Col E (5) = nama sekolah
     *  Row 4, Col E (5) = nama kelas
     *  Row 5, Col E (5) = pertemuan ke
     *  Row 9+           = data siswa
     *    Col B (2)  = nomor urut
     *    Col C (3)  = nama siswa
     *    Col D-F    = 3 nilai L0  → avg → round → clamp 1-5
     *    Col G-I    = 3 nilai L1
     *    Col J-L    = 3 nilai L2
     *    Col M-O    = 3 nilai L3
     *    Col P-R    = 3 nilai L4
     *    Col W (23) = rekomendasi
     *    Col X (24) = catatan
     */
    public function import(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);

        // Cari sheet "SIPENA KARAKTER" (case-insensitive)
        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strtolower(trim($name)) === 'sipena karakter') {
                $sheet = $spreadsheet->getSheetByName($name);
                break;
            }
        }

        if (! $sheet) {
            // fallback: sheet pertama
            $sheet = $spreadsheet->getActiveSheet();
            $this->warnings[] = 'Tab "SIPENA KARAKTER" tidak ditemukan, menggunakan sheet aktif.';
        }

        // --- Baca header ---
        $namaSekolahXls = trim((string) $sheet->getCellByColumnAndRow(5, 3)->getCalculatedValue());
        $kelasNamaXls   = strtoupper(trim((string) $sheet->getCellByColumnAndRow(5, 4)->getCalculatedValue()));
        $pertemuanXls   = (int) $sheet->getCellByColumnAndRow(5, 5)->getCalculatedValue();

        // Validasi sekolah
        if ($namaSekolahXls !== '' && strtolower($namaSekolahXls) !== strtolower($this->sekolah->nama)) {
            $this->sekolahMismatch = true;
            $this->warnings[] = "Nama sekolah di file (\"{$namaSekolahXls}\") tidak sesuai dengan sekolah Anda (\"{$this->sekolah->nama}\"). Data tetap diproses.";
        }

        if ($kelasNamaXls === '') {
            $this->warnings[] = 'Nama kelas (4E) kosong di file XLS.';
        }

        if ($pertemuanXls < 1 || $pertemuanXls > 16) {
            $this->skipped[] = ['nama' => '-', 'reason' => "Pertemuan ke ({$pertemuanXls}) tidak valid (harus 1–16)."];
            return;
        }

        $this->kelasNama = $kelasNamaXls;
        $this->pertemuan = $pertemuanXls;

        // Cari atau buat kelas
        $kelas = null;
        if ($kelasNamaXls !== '') {
            $kelas = Kelas::where('sekolah_id', $this->sekolah->id)
                ->where('nama', $kelasNamaXls)
                ->first();

            if (! $kelas) {
                $kelas = Kelas::create([
                    'sekolah_id' => $this->sekolah->id,
                    'nama'       => $kelasNamaXls,
                ]);
                $this->warnings[] = "Kelas \"{$kelasNamaXls}\" belum ada di database, otomatis ditambahkan.";
            }
        }

        // --- Baca data siswa mulai baris 9 ---
        $highestRow = $sheet->getHighestRow();

        for ($row = 9; $row <= $highestRow; $row++) {
            $nomorCell = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $namaSiswa = trim((string) $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue());

            // Skip baris yang tidak ada nomornya
            if ($nomorCell === null || $nomorCell === '') {
                continue;
            }

            // Skip jika nama kosong
            if ($namaSiswa === '') {
                $this->skipped[] = [
                    'nama'   => "(Baris {$row}, No. {$nomorCell})",
                    'reason' => 'Nama siswa kosong.',
                ];
                continue;
            }

            if (! $kelas) {
                $this->skipped[] = ['nama' => $namaSiswa, 'reason' => 'Kelas tidak dapat ditentukan (field 4E kosong).'];
                continue;
            }

            // Hitung rata-rata 3 nilai per level, lalu round & clamp 1-5
            $l0 = $this->avgLevel($sheet, $row, 4, 6);   // D-F
            $l1 = $this->avgLevel($sheet, $row, 7, 9);   // G-I
            $l2 = $this->avgLevel($sheet, $row, 10, 12); // J-L
            $l3 = $this->avgLevel($sheet, $row, 13, 15); // M-O
            $l4 = $this->avgLevel($sheet, $row, 16, 18); // P-R

            $rekomendasi = trim((string) $sheet->getCellByColumnAndRow(23, $row)->getCalculatedValue()); // W
            $catatan     = trim((string) $sheet->getCellByColumnAndRow(24, $row)->getCalculatedValue()); // X

            // Cari atau buat siswa
            $siswa = Siswa::where('kelas_id', $kelas->id)->where('nama', $namaSiswa)->first();
            $action = 'diperbarui';

            if (! $siswa) {
                $siswa = Siswa::create([
                    'kelas_id' => $kelas->id,
                    'nama'     => $namaSiswa,
                    'rata_poin' => 0,
                ]);
                $action = 'ditambahkan';
            }

            // Update catatan & rekomendasi jika ada
            $updateSiswa = [];
            if ($rekomendasi !== '') $updateSiswa['rekomendasi'] = $rekomendasi;
            if ($catatan     !== '') $updateSiswa['keterangan']  = $catatan;
            if ($updateSiswa) $siswa->update($updateSiswa);

            // Simpan penilaian
            Penilaian::updateOrCreate(
                ['siswa_id' => $siswa->id, 'pertemuan' => (string) $pertemuanXls],
                [
                    'L0' => $l0,
                    'L1' => $l1,
                    'L2' => $l2,
                    'L3' => $l3,
                    'L4' => $l4,
                ]
            );

            // Recalculate rata_poin
            $this->recalcRataPoin($siswa);

            $this->imported[] = [
                'nama'      => $namaSiswa,
                'pertemuan' => $pertemuanXls,
                'action'    => $action,
            ];
        }
    }

    /**
     * Hitung rata-rata nilai dari kolom startCol hingga endCol pada baris tertentu.
     * Menggunakan getCalculatedValue() agar formula cross-sheet ikut dievaluasi.
     * Nilai kosong/non-numerik diabaikan. Hasil di-clamp ke 1-5, atau null jika semua kosong.
     */
    private function avgLevel($sheet, int $row, int $startCol, int $endCol): ?int
    {
        $values = [];
        for ($col = $startCol; $col <= $endCol; $col++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            try {
                $v = $cell->getCalculatedValue();
            } catch (\Throwable) {
                $v = $cell->getValue();
            }
            if ($v !== null && $v !== '' && is_numeric($v)) {
                $values[] = (float) $v;
            }
        }

        if (empty($values)) return null;

        $avg = array_sum($values) / count($values);
        $rounded = (int) round($avg);
        return max(1, min(5, $rounded));
    }

    private function recalcRataPoin(Siswa $siswa): void
    {
        $all = Penilaian::where('siswa_id', $siswa->id)->get();
        if ($all->isEmpty()) { $siswa->update(['rata_poin' => 0]); return; }

        $total = $count = 0;
        foreach ($all as $p) {
            foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $l) {
                if ($p->{$l} !== null) { $total += (int) $p->{$l}; $count++; }
            }
        }
        $siswa->update(['rata_poin' => $count > 0 ? round($total / $count, 2) : 0]);
    }
}
