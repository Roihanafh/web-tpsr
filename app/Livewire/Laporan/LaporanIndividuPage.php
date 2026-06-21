<?php

namespace App\Livewire\Laporan;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporanIndividuPage extends Component
{
    public ?string $isGanjil    = null;   // '1', '0', atau null
    public mixed   $kelasId     = null;
    public string  $search      = '';
    public ?int    $selectedSiswaId = null;
    public bool    $showChart   = false;

    public function updatedIsGanjil(): void
    {
        $this->kelasId        = null;
        $this->selectedSiswaId = null;
        $this->showChart      = false;
    }

    public function updatedKelasId(): void
    {
        $this->selectedSiswaId = null;
        $this->showChart      = false;
    }

    public function showDetail(int $siswaId): void
    {
        $this->selectedSiswaId = $siswaId;
        $this->showChart       = true;
    }

    public function closeChart(): void
    {
        $this->selectedSiswaId = null;
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

        // Kelas options (filter berdasarkan semester)
        $kelasOptions = collect();
        if ($sekolah && $this->isGanjil !== null) {
            $kelasOptions = $sekolah->kelas()
                ->where('is_ganjil', (bool) $this->isGanjil)
                ->orderBy('nama')
                ->get();
        }

        // Siswa list
        $siswaList = collect();
        if ($sekolah && $this->kelasId) {
            $siswaList = Siswa::with('kelas')
                ->when($this->kelasId === 'all', function ($q) use ($sekolah) {
                    $q->whereHas('kelas', function ($qk) use ($sekolah) {
                        $qk->where('sekolah_id', $sekolah->id)
                           ->when($this->isGanjil !== null, fn ($qs) => $qs->where('is_ganjil', (bool) $this->isGanjil));
                    });
                }, function ($q) {
                    $q->where('kelas_id', $this->kelasId);
                })
                ->when(trim($this->search) !== '', fn ($q) => $q->where('nama', 'like', '%' . trim($this->search) . '%'))
                ->orderBy('nama')
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
                });
        }

        // Chart & PDF data
        $chartData     = null;
        $pdfData       = null;
        $selectedSiswa = null;

        if ($this->selectedSiswaId && $this->showChart) {
            $selectedSiswa = Siswa::with('kelas')->find($this->selectedSiswaId);

            if ($selectedSiswa) {
                $penilaianList = Penilaian::where('siswa_id', $this->selectedSiswaId)
                    ->orderBy('pertemuan')
                    ->get()
                    ->keyBy(fn ($p) => (int) $p->pertemuan);

                $labels = [];
                $values = [];
                for ($i = 1; $i <= 16; $i++) {
                    $labels[] = 'P' . $i;
                    if (isset($penilaianList[$i])) {
                        $p     = $penilaianList[$i];
                        [$sum, $cnt] = $this->sumLevelRow($p);
                        $values[] = $cnt > 0 ? round($sum / $cnt, 2) : null;
                    } else {
                        $values[] = null;
                    }
                }

                $pertemuanDinilai = $penilaianList->count();
                [$totalAll, $countAll] = $this->sumLevels($penilaianList);
                $rataLaporan = $countAll > 0 ? round($totalAll / $countAll, 2) : null;

                $chartData = [
                    'labels'            => $labels,
                    'values'            => $values,
                    'nama'              => $selectedSiswa->nama,
                    'kelas'             => $selectedSiswa->kelas?->nama,
                    'tahun_ajar'        => $selectedSiswa->kelas?->is_ganjil ? 'Ganjil' : 'Genap',
                    'rata_laporan'      => $rataLaporan,
                    'pertemuan_dinilai' => $pertemuanDinilai,
                ];

                $pdfData = [
                    'siswa'       => $selectedSiswa,
                    'pengajar'    => Auth::user()?->name ?? '-',
                    'sekolahNama' => $sekolah?->nama ?? '-',
                    'rataLaporan' => $rataLaporan ?? 0,
                    'status'      => self::getStatus($rataLaporan),
                    'semester'    => $selectedSiswa->kelas?->is_ganjil ? 'Ganjil' : 'Genap',
                ];
            }
        }

        return view('livewire.laporan.laporan-individu-page', [
            'kelasOptions'  => $kelasOptions,
            'siswaList'     => $siswaList,
            'chartData'     => $chartData,
            'pdfData'       => $pdfData,
            'selectedSiswa' => $selectedSiswa,
            'sekolah'       => $sekolah,
        ]);
    }

    /** Sum semua nilai L0-L4 dari collection penilaian. Returns [total, count]. */
    private function sumLevels($collection): array
    {
        $total = 0;
        $count = 0;
        foreach ($collection as $p) {
            [$s, $c] = $this->sumLevelRow($p);
            $total += $s;
            $count += $c;
        }
        return [$total, $count];
    }

    private function sumLevelRow($p): array
    {
        $sum = 0;
        $cnt = 0;
        foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $l) {
            if ($p->{$l} !== null) {
                $sum += (int) $p->{$l};
                $cnt++;
            }
        }
        return [$sum, $cnt];
    }
}
