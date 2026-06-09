<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect([
            ['Andi Wijaya', 'L', '5-A'],
            ['Budi Santoso', 'L', '5-A'],
            ['Citra Dewi', 'P', '5-B'],
            ['Dini Irawan', 'P', '5-B'],
        ]);
    }

    public function headings(): array
    {
        return ['nama_siswa', 'jenis_kelamin', 'kelas'];
    }
}
