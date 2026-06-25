<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Sekolah;
use App\Models\Siswa;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TpsrTemplateImport
{
    // Hasil import
    public array   $imported      = [];   // [{nama, action, pertemuan_count}]
    public array   $skipped       = [];   // [{nama, reason}]
    public array   $warnings      = [];
    public ?string $kelasNama     = null;
    public bool    $kelasIsNew    = false;
    public ?int    $lastPertemuan = null; // pertemuan tertinggi yang ada datanya

    private Sekolah $sekolah;

    public function __construct(Sekolah $sekolah)
    {
        $this->sekolah = $sekolah;
    }

    /**
     * Layout tab "Input_TPSR":
     *
     *   Baris 2, Kolom B (col=2) → nama kelas
     *
     *   Baris 5+ → data siswa:
     *     Kolom A  (col=1)   → nama siswa
     *     P1  L0=B(2)  L1=C(3)  L2=D(4)  L3=E(5)  L4=F(6)
     *     P2  L0=G(7)  L1=H(8)  L2=I(9)  L3=J(10) L4=K(11)
     *     ...
     *     Pn  L0 = 2 + (n-1)*5,  L1..L4 = L0+1..L0+4
     *     P16 L0=BW(77) L1=BX(78) L2=BY(79) L3=BZ(80) L4=CA(81=CC? no)
     *
     *   Verifikasi kolom CC:
     *     A=1..Z=26, AA=27..AZ=52, BA=53..BZ=78, CA=79, CB=80, CC=81
     *     P16 L4 = 2 + 15*5 + 4 = 81 = CC ✓
     */
    public function import(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);

        // Cari sheet "Input_TPSR" (case-insensitive)
        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strtolower(trim($name)) === 'input_tpsr') {
                $sheet = $spreadsheet->getSheetByName($name);
                break;
            }
        }

        if (! $sheet) {
            // fallback ke sheet pertama jika tab tidak ditemukan
            $sheet = $spreadsheet->getActiveSheet();
            $this->warnings[] = 'Tab "Input_TPSR" tidak ditemukan, menggunakan sheet aktif.';
        }

        // --- Baca nama kelas dari sel B2 (row=2, col=2) ---
        $kelasNamaRaw = trim((string) $sheet->getCellByColumnAndRow(2, 2)->getCalculatedValue());
        $kelasNama    = strtoupper($kelasNamaRaw);

        if ($kelasNama === '') {
            $this->warnings[] = 'Nama kelas (sel B2) kosong di file Excel.';
        }

        $this->kelasNama = $kelasNama;

        // Validasi kelas: cari di DB → gunakan jika ada, buat baru jika tidak
        $kelas = null;
        if ($kelasNama !== '') {
            $kelas = Kelas::where('sekolah_id', $this->sekolah->id)
                ->where('nama', $kelasNama)
                ->first();

            if ($kelas) {
                // Kelas ditemukan, gunakan kelas yang sudah ada
            } else {
                // Kelas tidak ditemukan, buat kelas baru
                $kelas = Kelas::create([
                    'sekolah_id' => $this->sekolah->id,
                    'nama'       => $kelasNama,
                ]);
                $this->kelasIsNew = true;
                $this->warnings[] = "Kelas \"{$kelasNama}\" tidak ditemukan, otomatis dibuat.";
            }
        }

        // --- Baca data siswa mulai baris 5, kolom A (col=1) ---
        $highestRow = $sheet->getHighestRow();

        for ($row = 5; $row <= $highestRow; $row++) {
            $namaSiswa = trim((string) $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue());

            // Skip baris dengan nama kosong
            if ($namaSiswa === '') {
                continue;
            }

            if (! $kelas) {
                $this->skipped[] = [
                    'nama'   => $namaSiswa,
                    'reason' => 'Kelas tidak dapat ditentukan (sel B2 kosong).',
                ];
                continue;
            }

            // Cari siswa di kelas tersebut, atau buat baru
            $siswa  = Siswa::where('kelas_id', $kelas->id)->where('nama', $namaSiswa)->first();
            $action = 'diperbarui';

            if (! $siswa) {
                $siswa = Siswa::create([
                    'kelas_id'  => $kelas->id,
                    'nama'      => $namaSiswa,
                    'rata_poin' => 0,
                ]);
                $action = 'ditambahkan';
            }

            $pertemuanDiimport = 0;

            // Baca 16 pertemuan
            // Pertemuan n: kolom L0 = 2 + (n-1)*5, L1..L4 = L0+1..L0+4
            for ($p = 1; $p <= 16; $p++) {
                $startCol = 2 + ($p - 1) * 5; // P1=2(B), P2=7(G), ..., P16=77(BW)

                $l0 = $this->readLevel($sheet, $row, $startCol);
                $l1 = $this->readLevel($sheet, $row, $startCol + 1);
                $l2 = $this->readLevel($sheet, $row, $startCol + 2);
                $l3 = $this->readLevel($sheet, $row, $startCol + 3);
                $l4 = $this->readLevel($sheet, $row, $startCol + 4);

                // Skip pertemuan jika semua level kosong
                if ($l0 === null && $l1 === null && $l2 === null && $l3 === null && $l4 === null) {
                    continue;
                }

                Penilaian::updateOrCreate(
                    ['siswa_id' => $siswa->id, 'pertemuan' => (string) $p],
                    [
                        'L0' => $l0,
                        'L1' => $l1,
                        'L2' => $l2,
                        'L3' => $l3,
                        'L4' => $l4,
                    ]
                );

                $pertemuanDiimport++;

                // Track pertemuan tertinggi yang ada datanya
                if ($this->lastPertemuan === null || $p > $this->lastPertemuan) {
                    $this->lastPertemuan = $p;
                }
            }

            // Hitung ulang rata_poin berdasarkan semua penilaian siswa
            $this->recalcRataPoin($siswa);

            $this->imported[] = [
                'nama'            => $namaSiswa,
                'action'          => $action,
                'pertemuan_count' => $pertemuanDiimport,
            ];
        }
    }

    /**
     * Baca nilai satu sel, kembalikan integer 1–4 atau null jika kosong/tidak valid.
     * Nilai valid sesuai enum database: '1','2','3','4'.
     */
    private function readLevel($sheet, int $row, int $col): ?int
    {
        $cell = $sheet->getCellByColumnAndRow($col, $row);
        try {
            $v = $cell->getCalculatedValue();
        } catch (\Throwable) {
            $v = $cell->getValue();
        }

        if ($v === null || $v === '') {
            return null;
        }

        if (! is_numeric($v)) {
            return null;
        }

        $int = (int) round((float) $v);

        // Enum database hanya menerima 1–4
        return ($int >= 1 && $int <= 4) ? $int : null;
    }

    private function recalcRataPoin(Siswa $siswa): void
    {
        $all = Penilaian::where('siswa_id', $siswa->id)->get();

        if ($all->isEmpty()) {
            $siswa->update(['rata_poin' => 0]);
            return;
        }

        $total = 0;
        $count = 0;

        foreach ($all as $p) {
            foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $l) {
                if ($p->{$l} !== null) {
                    $total += (int) $p->{$l};
                    $count++;
                }
            }
        }

        $siswa->update(['rata_poin' => $count > 0 ? round($total / $count, 2) : 0]);
    }
}
