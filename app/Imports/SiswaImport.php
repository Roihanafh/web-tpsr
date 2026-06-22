<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    private array $failures = [];
    private int   $inserted = 0;

    public function __construct(private readonly Sekolah $sekolah) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line      = $index + 2;
            $namaSiswa = trim((string) ($row['nama_siswa'] ?? $row['nama'] ?? ''));
            $kelasNama = strtoupper(trim((string) ($row['kelas'] ?? '')));

            if ($namaSiswa === '') { $this->addFailure($line, '-', 'Nama siswa wajib diisi.'); continue; }
            if (mb_strlen($namaSiswa) > 100) { $this->addFailure($line, $namaSiswa, 'Nama siswa maksimal 100 karakter.'); continue; }
            if ($kelasNama === '') { $this->addFailure($line, $namaSiswa, 'Kelas wajib diisi.'); continue; }

            $kelas = Kelas::where('sekolah_id', $this->sekolah->id)->where('nama', $kelasNama)->first();

            if (! $kelas) { $this->addFailure($line, $namaSiswa, "Kelas {$kelasNama} tidak ditemukan."); continue; }

            if (Siswa::where('kelas_id', $kelas->id)->where('nama', $namaSiswa)->exists()) {
                $this->addFailure($line, $namaSiswa, 'Siswa sudah ada di kelas tersebut.');
                continue;
            }

            Siswa::create(['kelas_id' => $kelas->id, 'nama' => $namaSiswa, 'rata_poin' => 0]);
            $this->inserted++;
        }
    }

    public function failures(): array    { return $this->failures; }
    public function insertedCount(): int { return $this->inserted; }

    private function addFailure(int $line, string $nama, string $message): void
    {
        $this->failures[] = ['line' => $line, 'nama' => $nama, 'message' => $message];
    }
}
