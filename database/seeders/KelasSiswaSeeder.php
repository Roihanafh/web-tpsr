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
        $tahunAjarGanjil = TahunAjar::firstOrCreate(
            ['nama' => '2025/2026 Ganjil']
        );

        $tahunAjarGenap = TahunAjar::firstOrCreate(
            ['nama' => '2025/2026 Genap']
        );

        // Buat Kelas 5-A di kedua semester
        $kelasAGanjil = Kelas::firstOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'tahun_ajar_id' => $tahunAjarGanjil->id,
                'nama' => '5-A'
            ]
        );

        $kelasAGenap = Kelas::firstOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'tahun_ajar_id' => $tahunAjarGenap->id,
                'nama' => '5-A'
            ]
        );

        // Buat Kelas 5-B di kedua semester
        $kelasBGanjil = Kelas::firstOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'tahun_ajar_id' => $tahunAjarGanjil->id,
                'nama' => '5-B'
            ]
        );

        $kelasBGenap = Kelas::firstOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'tahun_ajar_id' => $tahunAjarGenap->id,
                'nama' => '5-B'
            ]
        );

        // Isi Kelas 5-A dengan siswa di kedua semester
        $students = [
            ['nama' => 'Ahmad Fauzi', 'gender' => 'L', 'rata_poin' => 0.0],
            ['nama' => 'Budi Santoso', 'gender' => 'L', 'rata_poin' => 0.0],
            ['nama' => 'Citra Lestari', 'gender' => 'P', 'rata_poin' => 0.0],
            ['nama' => 'Dina Mariana', 'gender' => 'P', 'rata_poin' => 0.0],
            ['nama' => 'Eko Prasetyo', 'gender' => 'L', 'rata_poin' => 0.0],
        ];

        if ($kelasAGanjil->siswa()->count() === 0) {
            foreach ($students as $student) {
                $kelasAGanjil->siswa()->create($student);
            }
        }

        if ($kelasAGenap->siswa()->count() === 0) {
            foreach ($students as $student) {
                $kelasAGenap->siswa()->create($student);
            }
        }
    }
}
