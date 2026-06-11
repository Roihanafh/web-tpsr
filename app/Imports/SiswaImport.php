<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\TahunAjar;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    private array $failures = [];

    private int $inserted = 0;

    public function __construct(
        private readonly Sekolah $sekolah,
        private readonly string $selectedYear,
        private readonly TahunAjar $ganjil,
        private readonly TahunAjar $genap,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $namaSiswa = trim((string) ($row['nama_siswa'] ?? $row['nama'] ?? ''));
            $jenisKelamin = strtoupper(trim((string) ($row['jenis_kelamin'] ?? $row['gender'] ?? '')));
            $kelas = trim((string) ($row['kelas'] ?? $row['kelas_nama'] ?? ''));

            if ($namaSiswa === '') {
                $this->addFailure($line, '-', 'Nama siswa wajib diisi.');
                continue;
            }

            if (mb_strlen($namaSiswa) > 100) {
                $this->addFailure($line, $namaSiswa, 'Nama siswa maksimal 100 karakter.');
                continue;
            }

            // Map jenis kelamin
            if ($jenisKelamin === 'LAKI-LAKI' || $jenisKelamin === 'L') {
                $jenisKelamin = 'L';
            } elseif ($jenisKelamin === 'PEREMPUAN' || $jenisKelamin === 'P') {
                $jenisKelamin = 'P';
            } else {
                $this->addFailure($line, $namaSiswa, 'Jenis kelamin harus "L" atau "P".');
                continue;
            }

            if ($kelas === '') {
                $this->addFailure($line, $namaSiswa, 'Kelas wajib diisi.');
                continue;
            }

            $ganjilKelas = Kelas::where('nama', strtoupper($kelas))
                ->where('sekolah_id', $this->sekolah->id)
                ->where('tahun_ajar_id', $this->ganjil->id)
                ->first();

            $genapKelas = Kelas::where('nama', strtoupper($kelas))
                ->where('sekolah_id', $this->sekolah->id)
                ->where('tahun_ajar_id', $this->genap->id)
                ->first();

            if (! $ganjilKelas || ! $genapKelas) {
                $this->addFailure($line, $namaSiswa, "Kelas {$kelas} tidak lengkap untuk semester ganjil dan genap pada tahun ajaran {$this->selectedYear}.");
                continue;
            }

            $existsGanjil = Siswa::where('kelas_id', $ganjilKelas->id)
                ->where('nama', $namaSiswa)
                ->exists();

            $existsGenap = Siswa::where('kelas_id', $genapKelas->id)
                ->where('nama', $namaSiswa)
                ->exists();

            if ($existsGanjil || $existsGenap) {
                $this->addFailure($line, $namaSiswa, 'Data siswa sudah ada untuk kelas tersebut.');
                continue;
            }

            Siswa::create([
                'kelas_id' => $ganjilKelas->id,
                'nama' => $namaSiswa,
                'gender' => $jenisKelamin,
                'rata_poin' => 0,
            ]);

            Siswa::create([
                'kelas_id' => $genapKelas->id,
                'nama' => $namaSiswa,
                'gender' => $jenisKelamin,
                'rata_poin' => 0,
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
