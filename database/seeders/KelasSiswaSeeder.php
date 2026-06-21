<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSiswaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Buat Sekolah
        $sekolah = Sekolah::firstOrCreate(
            ['nama' => 'SD Turen 1'],
            ['alamat' => 'Jl. Kemuning No. 45, Turen']
        );

        Sekolah::firstOrCreate(
            ['nama' => 'SD Turen 2'],
            ['alamat' => 'Jl. Mawar No. 12, Turen']
        );

        // Hubungkan Guru ke Sekolah
        $guru = User::where('email', 'guru@example.com')->first();
        if ($guru) {
            $guru->update(['sekolah_id' => $sekolah->id]);
        }

        // 4 kelas: 5-A Ganjil, 5-A Genap, 5-B Ganjil, 5-B Genap
        $kelasDefs = [
            ['nama' => '5-A', 'is_ganjil' => true],
            ['nama' => '5-A', 'is_ganjil' => false],
            ['nama' => '5-B', 'is_ganjil' => true],
            ['nama' => '5-B', 'is_ganjil' => false],
        ];

        $siswaPerKelas = [
            '5-A' => [
                ['nama' => 'Ahmad Fauzi',   'rata_poin' => 0.0],
                ['nama' => 'Budi Santoso',  'rata_poin' => 0.0],
                ['nama' => 'Citra Lestari', 'rata_poin' => 0.0],
                ['nama' => 'Dina Mariana',  'rata_poin' => 0.0],
                ['nama' => 'Eko Prasetyo',  'rata_poin' => 0.0],
            ],
            '5-B' => [
                ['nama' => 'Fajar Nugroho', 'rata_poin' => 0.0],
                ['nama' => 'Gita Permata',  'rata_poin' => 0.0],
                ['nama' => 'Hendra Wijaya', 'rata_poin' => 0.0],
                ['nama' => 'Indah Safitri', 'rata_poin' => 0.0],
                ['nama' => 'Joko Susilo',   'rata_poin' => 0.0],
            ],
        ];

        foreach ($kelasDefs as $def) {
            $kelas = Kelas::firstOrCreate([
                'sekolah_id' => $sekolah->id,
                'nama'       => $def['nama'],
                'is_ganjil'  => $def['is_ganjil'],
            ]);

            if ($kelas->siswa()->count() === 0) {
                foreach ($siswaPerKelas[$def['nama']] as $student) {
                    $kelas->siswa()->create($student);
                }
            }
        }
    }
}
