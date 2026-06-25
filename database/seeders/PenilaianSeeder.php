<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PenilaianSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     * Setiap pertemuan memiliki 5 aspek penilaian (L0-L4), masing-masing bernilai 1-5.
     * rata_poin dihitung dari rata-rata semua nilai L0-L4 di semua pertemuan.
     */
    public function run(): void
    {
        $kelas = Kelas::where('nama', '5-A')->first();

        if (! $kelas) {
            return;
        }

        foreach ($kelas->siswa as $siswa) {
            // Bersihkan penilaian lama jika ada
            $siswa->penilaian()->delete();

            $totalNilai = 0;
            $jumlahNilai = 0;

            for ($pertemuan = 1; $pertemuan <= 16; $pertemuan++) {
                $scores = [
                    'L0' => rand(1, 4),
                    'L1' => rand(1, 4),
                    'L2' => rand(1, 4),
                    'L3' => rand(1, 4),
                    'L4' => rand(1, 4),
                ];

                $totalNilai  += array_sum($scores);
                $jumlahNilai += count($scores);

                $siswa->penilaian()->create(array_merge(
                    ['pertemuan' => (string) $pertemuan],
                    array_map('strval', $scores)
                ));
            }

            // rata_poin = rata-rata seluruh nilai L0-L4 di semua pertemuan
            $rataPoin = $jumlahNilai > 0 ? $totalNilai / $jumlahNilai : 0;
            $siswa->update(['rata_poin' => round($rataPoin, 2)]);
        }
    }
}
