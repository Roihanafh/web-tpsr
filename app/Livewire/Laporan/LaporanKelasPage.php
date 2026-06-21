<?php

namespace App\Livewire\Laporan;

use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporanKelasPage extends Component
{
    public ?string $isGanjil      = null;  // '1', '0', atau null
    public ?int    $selectedKelasId = null;
    public string  $search        = '';
    public bool    $showChart     = false;

    public function updatedIsGanjil(): void
    {
        $this->selectedKelasId = null;
        $this->showChart       = false;
    }

    public function showDetail(int $kelasId): void
    {
        $this->selectedKelasId = $kelasId;
        $this->showChart       = true;
    }

    public function closeChart(): void
    {
        $this->selectedKelasId = null;
        $this->showChart       = false;
    }

    public static function getStatus(?float $rata): string
    {
        if ($rata === null) return '-';
        return match (true) {
            $rata <= 1.00 => 'Perlu Perhatian',
            $rata <= 2.00 => 'Cukup',
            $rata <= 3.00 => 'Cukup Baik',
            $rata <= 4.00 => 'Baik',
            default       => 'Sangat Baik',
        };
    }

    public function render(): View
    {
        $sekolah = Auth::user()?->sekolah;

        // Tabel kelas
        $kelasList = collect();
        if ($sekolah) {
            $kelasList = $sekolah->kelas()
                ->when($this->isGanjil !== null, fn ($q) => $q->where('is_ganjil', (bool) $this->isGanjil))
                ->when(trim($this->search) !== '', fn ($q) => $q->where('nama', 'like', '%' . trim($this->search) . '%'))
                ->orderBy('nama')
                ->get();
        }

        // Detail kelas terpilih
        $siswaList     = collect();
        $kelasRataRata = 0.00;
        $chartData     = null;
        $pdfData       = null;
        $selectedKelas = null;

        if ($this->selectedKelasId && $this->showChart && $sekolah) {
            $selectedKelas = $sekolah->kelas()
                ->where('id', $this->selectedKelasId)
                ->first();

            if ($selectedKelas) {
                $siswaList = Siswa::where('kelas_id', $this->selectedKelasId)
                    ->get()
                    ->map(function ($siswa) {
                        $all = Penilaian::where('siswa_id', $siswa->id)->get();
                        if ($all->isEmpty()) {
                            $siswa->rata_laporan      = null;
                            $siswa->pertemuan_dinilai = 0;
                            $siswa->status_laporan    = '-';
                        } else {
                            [$total, $count] = $this->sumLevels($all);
                            $siswa->rata_laporan      = $count > 0 ? round($total / $count, 2) : null;
                            $siswa->pertemuan_dinilai = $all->count();
                            $siswa->status_laporan    = self::getStatus($siswa->rata_laporan);
                        }
                        return $siswa;
                    })
                    ->sortByDesc(fn ($s) => $s->rata_laporan ?? -1)
                    ->values();

                $rated         = $siswaList->filter(fn ($s) => $s->rata_laporan !== null);
                $kelasRataRata = $rated->isEmpty() ? 0.00 : round($rated->avg('rata_laporan'), 2);

                // Chart per pertemuan: rata-rata L0-L4 di semua siswa
                $siswaIds     = $siswaList->pluck('id');
                $allPenilaian = Penilaian::whereIn('siswa_id', $siswaIds)->get()->groupBy('pertemuan');

                $labels = [];
                $values = [];
                for ($i = 1; $i <= 16; $i++) {
                    $labels[] = 'P' . $i;
                    $meet = $allPenilaian->get($i);
                    if ($meet && $meet->isNotEmpty()) {
                        [$sum, $cnt] = $this->sumLevels($meet);
                        $values[] = $cnt > 0 ? round($sum / $cnt, 2) : null;
                    } else {
                        $values[] = null;
                    }
                }

                $semester  = $selectedKelas->is_ganjil ? 'Ganjil' : 'Genap';

                $chartData = [
                    'labels'      => $labels,
                    'values'      => $values,
                    'kelas'       => $selectedKelas->nama,
                    'tahun_ajar'  => $semester,
                    'rata_kelas'  => $kelasRataRata,
                    'siswa_count' => $siswaList->count(),
                ];

                $pdfData = [
                    'kelas'       => $selectedKelas,
                    'pengajar'    => Auth::user()?->name ?? '-',
                    'sekolahNama' => $sekolah->nama ?? '-',
                    'rataKelas'   => $kelasRataRata,
                    'siswaList'   => $siswaList,
                    'semester'    => $semester,
                ];
            }
        }

        return view('livewire.laporan.laporan-kelas-page', [
            'kelasList'     => $kelasList,
            'sekolah'       => $sekolah,
            'siswaList'     => $siswaList,
            'kelasRataRata' => $kelasRataRata,
            'chartData'     => $chartData,
            'pdfData'       => $pdfData,
            'selectedKelas' => $selectedKelas,
            'semesterLabel' => $this->isGanjil === null ? 'Semua Semester' : ($this->isGanjil === '1' ? 'Ganjil' : 'Genap'),
        ]);
    }

    private function sumLevels($collection): array
    {
        $total = 0;
        $count = 0;
        foreach ($collection as $p) {
            foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $l) {
                if ($p->{$l} !== null) {
                    $total += (int) $p->{$l};
                    $count++;
                }
            }
        }
        return [$total, $count];
    }
}
