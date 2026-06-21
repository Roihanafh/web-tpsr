<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Siswa;
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

        // Buat Kelas — tidak ada tahun ajar, hanya sekolah_id + nama
        $kelasA = Kelas::firstOrCreate([
            'sekolah_id' => $sekolah->id,
            'nama'       => '5-A',
        ]);

        $kelasB = Kelas::firstOrCreate([
            'sekolah_id' => $sekolah->id,
            'nama'       => '5-B',
        ]);

        // Isi Kelas 5-A dengan siswa
        $studentsA = [
            ['nama' => 'Ahmad Fauzi',   'rata_poin' => 0.0],
            ['nama' => 'Budi Santoso',  'rata_poin' => 0.0],
            ['nama' => 'Citra Lestari', 'rata_poin' => 0.0],
            ['nama' => 'Dina Mariana',  'rata_poin' => 0.0],
            ['nama' => 'Eko Prasetyo',  'rata_poin' => 0.0],
        ];

        if ($kelasA->siswa()->count() === 0) {
            foreach ($studentsA as $student) {
                $kelasA->siswa()->create($student);
            }
        }

        // Isi Kelas 5-B dengan siswa
        $studentsB = [
            ['nama' => 'Fajar Nugroho',  'rata_poin' => 0.0],
            ['nama' => 'Gita Permata',   'rata_poin' => 0.0],
            ['nama' => 'Hendra Wijaya',  'rata_poin' => 0.0],
            ['nama' => 'Indah Safitri',  'rata_poin' => 0.0],
            ['nama' => 'Joko Susilo',    'rata_poin' => 0.0],
        ];

        if ($kelasB->siswa()->count() === 0) {
            foreach ($studentsB as $student) {
                $kelasB->siswa()->create($student);
            }
        }
    }
}
