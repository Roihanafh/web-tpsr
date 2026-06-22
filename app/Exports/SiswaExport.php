<?php

namespace App\Exports;

use App\Models\Sekolah;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaExport implements FromCollection, WithHeadings
{
    public function __construct(
        private readonly Sekolah $sekolah,
        private readonly ?string $kelasNama = null,
    ) {}

    public function collection(): Collection
    {
        return Siswa::query()
            ->with('kelas')
            ->whereHas('kelas', function ($q) {
                $q->where('sekolah_id', $this->sekolah->id)
                  ->when($this->kelasNama, fn ($q2) => $q2->where('nama', $this->kelasNama));
            })
            ->orderBy('nama')
            ->get()
            ->map(fn (Siswa $siswa) => [
                'nama_siswa' => $siswa->nama,
                'kelas'      => $siswa->kelas?->nama,
                'rata_poin'  => $siswa->rata_poin,
            ]);
    }

    public function headings(): array
    {
        return ['nama_siswa', 'kelas', 'rata_poin'];
    }
}
