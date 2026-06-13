<?php

namespace App\Livewire\Laporan;

use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporanKelasPage extends Component
{
    public mixed $tahunAjarId = null;

    public ?int $selectedKelasId = null;

    public string $search = '';

    public bool $showChart = false;

    public function updatedTahunAjarId(): void
    {
        $this->selectedKelasId = null;
        $this->showChart = false;
    }

    public function showDetail(int $kelasId): void
    {
        $this->selectedKelasId = $kelasId;
        $this->showChart = true;
    }

    public function closeChart(): void
    {
        $this->selectedKelasId = null;
        $this->showChart = false;
    }

    /**
     * Tentukan status berdasarkan rata-rata (5 status)
     */
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

        $tahunAjarOptions = TahunAjar::getSorted();

        // Data list kelas di tabel utama
        $kelasList = collect();
        if ($sekolah && $this->tahunAjarId) {
            $kelasList = $sekolah->kelas()
                ->when(str_starts_with((string)$this->tahunAjarId, 'year:'), function ($q) {
                    $yearPrefix = str_replace('year:', '', $this->tahunAjarId);
                    $q->whereHas('tahunAjar', function ($qt) use ($yearPrefix) {
                        $qt->where('nama', 'like', $yearPrefix . ' %');
                    });
                }, function ($q) {
                    $q->where('tahun_ajar_id', $this->tahunAjarId);
                })
                ->when(trim($this->search) !== '', function ($q) {
                    $search = trim($this->search);
                    $q->where('nama', 'like', '%' . $search . '%');
                })
                ->orderBy('nama')
                ->get();
        }

        // Tentukan label tahun ajaran untuk UI
        $tahunAjarLabel = '';
        if ($this->tahunAjarId) {
            if (str_starts_with((string)$this->tahunAjarId, 'year:')) {
                $tahunAjarLabel = str_replace('year:', '', $this->tahunAjarId);
            } else {
                $tahunAjarLabel = $tahunAjarOptions->firstWhere('id', (int)$this->tahunAjarId)?->nama ?? '';
            }
        }

        // Detail data kelas terpilih
        $siswaList = collect();
        $kelasRataRata = 0.00;
        $chartData = null;
        $pdfData = null;
        $selectedKelas = null;

        if ($this->selectedKelasId && $this->showChart && $sekolah) {
            $selectedKelas = $sekolah->kelas()
                ->with('tahunAjar')
                ->where('id', $this->selectedKelasId)
                ->first();

            if ($selectedKelas) {
                // Ambil daftar siswa kelas tersebut
                $siswaList = Siswa::where('kelas_id', $this->selectedKelasId)
                    ->get()
                    ->map(function ($siswa) {
                        $penilaianList = Penilaian::where('siswa_id', $siswa->id)->get();
                        if ($penilaianList->isEmpty()) {
                            $siswa->rata_laporan      = null;
                            $siswa->pertemuan_dinilai = 0;
                            $siswa->status_laporan    = '-';
                        } else {
                            $totalLevel               = $penilaianList->sum(fn ($p) => (int) $p->level);
                            $pertemuanDinilai         = $penilaianList->count();
                            $siswa->rata_laporan      = round($totalLevel / $pertemuanDinilai, 2);
                            $siswa->pertemuan_dinilai = $pertemuanDinilai;
                            $siswa->status_laporan    = self::getStatus($siswa->rata_laporan);
                        }
                        return $siswa;
                    });

                // Urutkan berdasarkan rata-rata poin tertinggi (null diletakkan di paling bawah)
                $siswaList = $siswaList->sortByDesc(function ($siswa) {
                    return $siswa->rata_laporan ?? -1;
                })->values();

                // Hitung rata-rata kelas (hanya dari siswa yang memiliki nilai)
                $ratedStudents = $siswaList->filter(fn($s) => $s->rata_laporan !== null);
                $kelasRataRata = $ratedStudents->isEmpty() ? 0.00 : round($ratedStudents->avg('rata_laporan'), 2);

                // Hitung data chart (rata-rata kelas per pertemuan P1-P16)
                $siswaIds = $siswaList->pluck('id');
                $allPenilaian = Penilaian::whereIn('siswa_id', $siswaIds)
                    ->get()
                    ->groupBy('pertemuan');

                $labels = [];
                $values = [];
                for ($i = 1; $i <= 16; $i++) {
                    $labels[] = 'P' . $i;
                    $meetingPenilaian = $allPenilaian->get($i);
                    if ($meetingPenilaian && $meetingPenilaian->isNotEmpty()) {
                        $values[] = round($meetingPenilaian->avg('level'), 2);
                    } else {
                        $values[] = null;
                    }
                }

                $chartData = [
                    'labels'       => $labels,
                    'values'       => $values,
                    'kelas'        => $selectedKelas->nama,
                    'tahun_ajar'   => $selectedKelas->tahunAjar?->nama,
                    'rata_kelas'   => $kelasRataRata,
                    'siswa_count'  => $siswaList->count(),
                ];

                $pdfData = [
                    'kelas'        => $selectedKelas,
                    'pengajar'     => Auth::user()?->name ?? '-',
                    'sekolahNama'  => $sekolah->nama ?? '-',
                    'rataKelas'    => $kelasRataRata,
                    'siswaList'    => $siswaList,
                ];
            }
        }

        return view('livewire.laporan.laporan-kelas-page', [
            'tahunAjarOptions' => $tahunAjarOptions,
            'kelasList'        => $kelasList,
            'sekolah'          => $sekolah,
            'siswaList'        => $siswaList,
            'kelasRataRata'    => $kelasRataRata,
            'chartData'        => $chartData,
            'pdfData'          => $pdfData,
            'selectedKelas'    => $selectedKelas,
            'tahunAjarLabel'   => $tahunAjarLabel,
        ]);
    }
}

