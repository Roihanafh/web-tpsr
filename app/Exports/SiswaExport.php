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
        private readonly ?int $kelasId = null,
        private readonly ?int $tahunAjarId = null,
    ) {}

    public function collection(): Collection
    {
        return Siswa::query()
            ->with('kelas')
            ->whereHas('kelas', function ($query) {
                $query->where('sekolah_id', $this->sekolah->id)
                    ->when($this->tahunAjarId, fn ($query) => $query->where('tahun_ajar_id', $this->tahunAjarId));
            })
            ->when($this->kelasId, fn ($query) => $query->where('kelas_id', $this->kelasId))
            ->orderBy('nama')
            ->get()
            ->map(fn (Siswa $siswa) => [
                'nama_siswa' => $siswa->nama,
                'jenis_kelamin' => $siswa->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                'kelas' => $siswa->kelas?->nama,
                'rata_poin' => $siswa->rata_poin,
            ]);
    }

    public function headings(): array
    {
        return ['nama_siswa', 'jenis_kelamin', 'kelas', 'rata_poin'];
    }
}
