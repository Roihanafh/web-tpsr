<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Sekolah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KelasExport implements FromCollection, WithHeadings
{
    public function __construct(
        private readonly Sekolah $sekolah,
        private readonly ?bool $isGanjil = null,
    ) {}

    public function collection(): Collection
    {
        return Kelas::query()
            ->where('sekolah_id', $this->sekolah->id)
            ->when($this->isGanjil !== null, fn ($q) => $q->where('is_ganjil', $this->isGanjil))
            ->orderBy('nama')
            ->get()
            ->map(fn (Kelas $kelas) => [
                'nama_kelas' => $kelas->nama,
                'semester'   => $kelas->is_ganjil ? 'Ganjil' : 'Genap',
            ]);
    }

    public function headings(): array
    {
        return ['nama_kelas', 'semester'];
    }
}
