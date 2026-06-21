<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Sekolah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasImport implements ToCollection, WithHeadingRow
{
    private array $failures = [];
    private int $inserted   = 0;

    public function __construct(
        private readonly Sekolah $sekolah,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line      = $index + 2;
            $namaKelas = strtoupper(trim((string) ($row['nama_kelas'] ?? $row['kelas'] ?? '')));
            $semester  = strtolower(trim((string) ($row['semester'] ?? '')));

            if ($namaKelas === '') {
                $this->addFailure($line, '-', 'Nama kelas wajib diisi.');
                continue;
            }

            if (mb_strlen($namaKelas) > 20) {
                $this->addFailure($line, $namaKelas, 'Nama kelas maksimal 20 karakter.');
                continue;
            }

            if (! in_array($semester, ['ganjil', 'genap'])) {
                $this->addFailure($line, $namaKelas, 'Semester harus "ganjil" atau "genap".');
                continue;
            }

            $isGanjil = $semester === 'ganjil';

            $exists = Kelas::where('sekolah_id', $this->sekolah->id)
                ->where('nama', $namaKelas)
                ->where('is_ganjil', $isGanjil)
                ->exists();

            if ($exists) {
                $this->addFailure($line, $namaKelas, 'Kelas sudah ada untuk semester tersebut.');
                continue;
            }

            Kelas::create([
                'sekolah_id' => $this->sekolah->id,
                'nama'       => $namaKelas,
                'is_ganjil'  => $isGanjil,
            ]);

            $this->inserted++;
        }
    }

    public function failures(): array { return $this->failures; }
    public function insertedCount(): int { return $this->inserted; }

    private function addFailure(int $line, string $nama, string $message): void
    {
        $this->failures[] = ['line' => $line, 'nama' => $nama, 'message' => $message];
    }
}
