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
        private readonly ?int $tahunAjarId = null,
    ) {}

    public function collection(): Collection
    {
        return Kelas::query()
            ->with('tahunAjar')
            ->where('sekolah_id', $this->sekolah->id)
            ->when($this->tahunAjarId, fn ($query) => $query->where('tahun_ajar_id', $this->tahunAjarId))
            ->orderBy('nama')
            ->get()
            ->map(fn (Kelas $kelas) => [
                'nama_kelas' => $kelas->nama,
                'tahun_ajaran' => $kelas->tahunAjar?->nama,
            ]);
    }

    public function headings(): array
    {
        return ['nama_kelas', 'tahun_ajaran'];
    }
}
