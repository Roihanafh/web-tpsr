<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KelasTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect([
            ['5-A', '2025/2026'],
            ['5-B', '2025/2026'],
        ]);
    }

    public function headings(): array
    {
        return ['nama_kelas', 'tahun_ajaran'];
    }
}
