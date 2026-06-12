<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\TahunAjar;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSiswaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat Sekolah jika belum ada
        $sekolah = Sekolah::firstOrCreate(
            ['nama' => 'SD Turen 1'],
            ['alamat' => 'Jl. Kemuning No. 45, Turen']
        );

        // Buat Sekolah kedua (tanpa kelas dan siswa)
        Sekolah::firstOrCreate(
            ['nama' => 'SD Turen 2'],
            ['alamat' => 'Jl. Mawar No. 12, Turen']
        );

        // Hubungkan Guru ke Sekolah tersebut
        $guru = User::where('email', 'guru@example.com')->first();
        if ($guru) {
            $guru->update(['sekolah_id' => $sekolah->id]);
        }

        // Buat Tahun Ajar
        $tahunAjar = TahunAjar::firstOrCreate(
            ['nama' => '2025/2026 Ganjil']
        );

        // Buat 2 Kelas (Kelas A berisi 5 siswa, Kelas B kosong)
        $kelasA = Kelas::firstOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'tahun_ajar_id' => $tahunAjar->id,
                'nama' => '5-A'
            ]
        );

        $kelasB = Kelas::firstOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'tahun_ajar_id' => $tahunAjar->id,
                'nama' => '5-B'
            ]
        );

        // 5. Isi Kelas 5-A dengan 5 siswa
        if ($kelasA->siswa()->count() === 0) {
            $students = [
                ['nama' => 'Ahmad Fauzi', 'gender' => 'L', 'rata_poin' => 0.0],
                ['nama' => 'Budi Santoso', 'gender' => 'L', 'rata_poin' => 0.0],
                ['nama' => 'Citra Lestari', 'gender' => 'P', 'rata_poin' => 0.0],
                ['nama' => 'Dina Mariana', 'gender' => 'P', 'rata_poin' => 0.0],
                ['nama' => 'Eko Prasetyo', 'gender' => 'L', 'rata_poin' => 0.0],
            ];

            foreach ($students as $student) {
                $kelasA->siswa()->create($student);
            }
        }
    }
}
