<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Sekolah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KelasExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Sekolah $sekolah) {}

    public function collection(): Collection
    {
        return Kelas::query()
            ->where('sekolah_id', $this->sekolah->id)
            ->orderBy('nama')
            ->get()
            ->map(fn (Kelas $kelas) => ['nama_kelas' => $kelas->nama]);
    }

    public function headings(): array
    {
        return ['nama_kelas'];
    }
}
