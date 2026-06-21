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
        private readonly ?string $isGanjil = null,  // '1', '0', atau null
    ) {}

    public function collection(): Collection
    {
        return Siswa::query()
            ->with(['kelas'])
            ->whereHas('kelas', function ($query) {
                $query->where('sekolah_id', $this->sekolah->id)
                    ->when($this->isGanjil !== null, fn ($q) => $q->where('is_ganjil', (bool) $this->isGanjil))
                    ->when($this->kelasNama, fn ($q) => $q->where('nama', $this->kelasNama));
            })
            ->orderBy('nama')
            ->get()
            ->map(fn (Siswa $siswa) => [
                'nama_siswa' => $siswa->nama,
                'kelas'      => $siswa->kelas?->nama,
                'semester'   => $siswa->kelas?->is_ganjil ? 'Ganjil' : 'Genap',
                'rata_poin'  => $siswa->rata_poin,
            ]);
    }

    public function headings(): array
    {
        return ['nama_siswa', 'kelas', 'semester', 'rata_poin'];
    }
}
