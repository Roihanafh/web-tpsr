<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PenilaianSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari kelas 5-A
        $kelasA = Kelas::where('nama', '5-A')->first();

        if ($kelasA) {
            foreach ($kelasA->siswa as $siswa) {
                // Bersihkan penilaian lama jika ada
                $siswa->penilaian()->delete();

                $totalLevel = 0;

                for ($pertemuan = 1; $pertemuan <= 16; $pertemuan++) {
                    // Acak nilai L0 - L5 (0 sampai 5)
                    $level = (string) rand(0, 5);

                    $totalLevel += (int) $level;

                    $siswa->penilaian()->create([
                        'pertemuan' => (string) $pertemuan,
                        'level' => $level,
                    ]);
                }

                // Update rata-rata poin siswa (selalu dibagi 16 pertemuan)
                $rataPoin = $totalLevel / 16;
                $siswa->update(['rata_poin' => round($rataPoin, 2)]);
            }
        }
    }
}
