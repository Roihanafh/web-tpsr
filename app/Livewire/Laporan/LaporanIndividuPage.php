<?php

namespace App\Livewire\Laporan;

use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporanIndividuPage extends Component
{
    // Filter — sama persis pola Assessment: tahun ajar dulu, baru kelas
    public ?int $tahunAjarId = null;

    public mixed $kelasId = null;

    public string $search = '';

    public ?int $selectedSiswaId = null;

    public bool $showChart = false;

    // Reset kelas setiap kali tahun ajar berubah — sama persis Assessment
    public function updatedTahunAjarId(): void
    {
        $this->kelasId = null;
        $this->selectedSiswaId = null;
        $this->showChart = false;
    }

    public function updatedKelasId(): void
    {
        $this->selectedSiswaId = null;
        $this->showChart = false;
    }

    public function updatedSearch(): void
    {
        // Search tidak mereset chart
    }

    public function showDetail(int $siswaId): void
    {
        $this->selectedSiswaId = $siswaId;
        $this->showChart = true;
    }

    public function closeChart(): void
    {
        $this->selectedSiswaId = null;
        $this->showChart = false;
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

        $tahunAjarOptions = TahunAjar::orderByDesc('id')->get();

        // Kelas hanya muncul setelah tahun ajar dipilih
        $kelasOptions = collect();
        if ($sekolah && $this->tahunAjarId) {
            $kelasOptions = $sekolah->kelas()
                ->where('tahun_ajar_id', $this->tahunAjarId)
                ->orderBy('nama')
                ->get();
        }

        // Data tabel hanya muncul setelah kelas dipilih
        $siswaList = collect();
        if ($sekolah && $this->kelasId) {
            $siswaList = Siswa::with('kelas.tahunAjar')
                ->when($this->kelasId === 'all', function ($q) use ($sekolah) {
                    $q->whereHas('kelas', function ($qk) use ($sekolah) {
                        $qk->where('sekolah_id', $sekolah->id)
                           ->where('tahun_ajar_id', $this->tahunAjarId);
                    });
                }, function ($q) {
                    $q->where('kelas_id', $this->kelasId);
                })
                ->when(trim($this->search) !== '', function ($q) {
                    $search = trim($this->search);
                    $q->where('nama', 'like', '%' . $search . '%');
                })
                ->orderBy('id')
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
        }

        // Chart + PDF data
        $chartData   = null;
        $pdfData     = null;
        $selectedSiswa = null;

        if ($this->selectedSiswaId && $this->showChart) {
            $selectedSiswa = Siswa::with('kelas.tahunAjar')->find($this->selectedSiswaId);

            if ($selectedSiswa) {
                $penilaianList = Penilaian::where('siswa_id', $this->selectedSiswaId)
                    ->orderBy('pertemuan')
                    ->get()
                    ->keyBy(fn ($p) => (int) $p->pertemuan);

                $labels = [];
                $values = [];
                for ($i = 1; $i <= 16; $i++) {
                    $labels[] = 'P' . $i;
                    $values[] = isset($penilaianList[$i]) ? (int) $penilaianList[$i]->level : null;
                }

                $pertemuanDinilai = $penilaianList->count();
                $totalLevel       = $penilaianList->sum(fn ($p) => (int) $p->level);
                $rataLaporan      = $pertemuanDinilai > 0
                    ? round($totalLevel / $pertemuanDinilai, 2)
                    : null;

                // Hitung jumlah perolehan per level (L0–L5)
                $levelCount = [];
                for ($lvl = 0; $lvl <= 5; $lvl++) {
                    $levelCount[$lvl] = $penilaianList->filter(fn ($p) => (int) $p->level === $lvl)->count();
                }

                $chartData = [
                    'labels'            => $labels,
                    'values'            => $values,
                    'nama'              => $selectedSiswa->nama,
                    'kelas'             => $selectedSiswa->kelas?->nama,
                    'tahun_ajar'        => $selectedSiswa->kelas?->tahunAjar?->nama,
                    'rata_laporan'      => $rataLaporan,
                    'pertemuan_dinilai' => $pertemuanDinilai,
                ];

                // Data untuk PDF template
                $pdfData = [
                    'siswa'       => $selectedSiswa,
                    'pengajar'    => Auth::user()?->name ?? '-',
                    'sekolahNama' => Auth::user()?->sekolah?->nama ?? '-',
                    'levelCount'  => $levelCount,
                    'rataLaporan' => $rataLaporan ?? 0,
                    'status'      => self::getStatus($rataLaporan),
                ];
            }
        }

        return view('livewire.laporan.laporan-individu-page', [
            'tahunAjarOptions' => $tahunAjarOptions,
            'kelasOptions'     => $kelasOptions,
            'siswaList'        => $siswaList,
            'chartData'        => $chartData,
            'pdfData'          => $pdfData,
            'selectedSiswa'    => $selectedSiswa,
            'sekolah'          => $sekolah,
        ]);
    }
}
