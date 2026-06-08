<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\TahunAjar;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasImport implements ToCollection, WithHeadingRow
{
    private array $failures = [];

    private int $inserted = 0;

    public function __construct(
        private readonly Sekolah $sekolah,
        private readonly TahunAjar $selectedTahunAjar,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $namaKelas = strtoupper(trim((string) ($row['nama_kelas'] ?? $row['kelas'] ?? '')));
            $tahunAjar = trim((string) ($row['tahun_ajaran'] ?? $row['tahun_ajar'] ?? ''));

            if ($namaKelas === '') {
                $this->addFailure($line, '-', 'Nama kelas wajib diisi.');
                continue;
            }

            if (mb_strlen($namaKelas) > 5) {
                $this->addFailure($line, $namaKelas, 'Nama kelas maksimal 5 karakter.');
                continue;
            }

            if ($tahunAjar === '') {
                $this->addFailure($line, $namaKelas, 'Tahun ajaran wajib diisi.');
                continue;
            }

            $tahunAjarModel = TahunAjar::where('nama', $tahunAjar)->first();

            if (! $tahunAjarModel) {
                $this->addFailure($line, $namaKelas, 'Tahun ajaran tidak ada di database.');
                continue;
            }

            if ($tahunAjarModel->isNot($this->selectedTahunAjar)) {
                $this->addFailure($line, $namaKelas, 'Tahun ajaran pada file tidak sesuai dengan tahun ajaran yang dipilih.');
                continue;
            }

            $exists = Kelas::where('sekolah_id', $this->sekolah->id)
                ->where('tahun_ajar_id', $this->selectedTahunAjar->id)
                ->where('nama', $namaKelas)
                ->exists();

            if ($exists) {
                $this->addFailure($line, $namaKelas, 'Data kelas sudah ada untuk sekolah dan tahun ajaran tersebut.');
                continue;
            }

            Kelas::create([
                'sekolah_id' => $this->sekolah->id,
                'tahun_ajar_id' => $this->selectedTahunAjar->id,
                'nama' => $namaKelas,
            ]);

            $this->inserted++;
        }
    }

    public function failures(): array
    {
        return $this->failures;
    }

    public function insertedCount(): int
    {
        return $this->inserted;
    }

    private function addFailure(int $line, string $nama, string $message): void
    {
        $this->failures[] = [
            'line' => $line,
            'nama' => $nama,
            'message' => $message,
        ];
    }
}
