<?php

namespace App\Livewire\Laporan;

use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporanKelasPage extends Component
{
    public ?int  $selectedKelasId = null;
    public string $search         = '';
    public bool   $showChart      = false;

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
            $rata <= 1.00 => 'Kurang Sekali',
            $rata <= 2.00 => 'Kurang',
            $rata <= 3.00 => 'Sedang',
            $rata <= 4.00 => 'Baik',
            default       => 'Baik Sekali',
        };
    }

    public function render(): View
    {
        $sekolah = Auth::user()?->sekolah;

        $kelasList = $sekolah
            ? $sekolah->kelas()
                ->when(trim($this->search) !== '', fn ($q) => $q->where('nama', 'like', '%' . trim($this->search) . '%'))
                ->orderBy('nama')->get()
            : collect();

        $siswaList = collect();
        $kelasRataRata = 0.00;
        $chartData = $pdfData = $selectedKelas = null;

        if ($this->selectedKelasId && $this->showChart && $sekolah) {
            $selectedKelas = $sekolah->kelas()->where('id', $this->selectedKelasId)->first();

            if ($selectedKelas) {
                $siswaList = Siswa::where('kelas_id', $this->selectedKelasId)
                    ->get()
                    ->map(function ($siswa) {
                        $all = Penilaian::where('siswa_id', $siswa->id)->get();
                        if ($all->isEmpty()) {
                            $siswa->rata_laporan = $siswa->pertemuan_dinilai = null;
                            $siswa->status_laporan = '-';
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

                $allPenilaian = Penilaian::whereIn('siswa_id', $siswaList->pluck('id'))->get()->groupBy('pertemuan');
                $labels = $values = [];
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

                $chartData = [
                    'labels'      => $labels,
                    'values'      => $values,
                    'kelas'       => $selectedKelas->nama,
                    'tahun_ajar'  => $selectedKelas->nama,
                    'rata_kelas'  => $kelasRataRata,
                    'siswa_count' => $siswaList->count(),
                ];

                $pdfData = [
                    'kelas'       => $selectedKelas,
                    'pengajar'    => Auth::user()?->name ?? '-',
                    'sekolahNama' => $sekolah->nama ?? '-',
                    'rataKelas'   => $kelasRataRata,
                    'siswaList'   => $siswaList,
                    'semester'    => $selectedKelas->nama,
                ];
            }
        }

        return view('livewire.laporan.laporan-kelas-page', compact(
            'kelasList', 'sekolah', 'siswaList', 'kelasRataRata', 'chartData', 'pdfData', 'selectedKelas'
        ) + ['semesterLabel' => 'Semua Kelas']);
    }

    private function sumLevels($collection): array
    {
        $total = $count = 0;
        foreach ($collection as $p) {
            foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $l) {
                if ($p->{$l} !== null) { $total += (int) $p->{$l}; $count++; }
            }
        }
        return [$total, $count];
    }
}
