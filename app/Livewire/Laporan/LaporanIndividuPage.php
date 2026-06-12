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

    public ?int $kelasId = null;

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
        // Search tidak mereset chart — user tetap bisa lihat grafik sambil mencari
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

    public function render(): View
    {
        $sekolah = Auth::user()?->sekolah;

        $tahunAjarOptions = TahunAjar::orderByDesc('id')->get();

        // Kelas hanya muncul setelah tahun ajar dipilih — sama persis Assessment
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
                ->where('kelas_id', $this->kelasId)
                ->when(trim($this->search) !== '', function ($q) {
                    $search = trim($this->search);
                    $q->where('nama', 'like', '%' . $search . '%');
                })
                ->orderBy('id')
                ->get()
                ->map(function ($siswa) {
                    // Hitung rata-rata fresh dari penilaian siswa ini:
                    // total level dibagi jumlah pertemuan yang sudah dinilai
                    $penilaianList = Penilaian::where('siswa_id', $siswa->id)->get();

                    if ($penilaianList->isEmpty()) {
                        $siswa->rata_laporan = null;
                        $siswa->pertemuan_dinilai = 0;
                    } else {
                        $totalLevel       = $penilaianList->sum(fn ($p) => (int) $p->level);
                        $pertemuanDinilai = $penilaianList->count();
                        $siswa->rata_laporan      = round($totalLevel / $pertemuanDinilai, 2);
                        $siswa->pertemuan_dinilai = $pertemuanDinilai;
                    }

                    return $siswa;
                });
        }

        // Chart data
        $chartData = null;
        $selectedSiswa = null;

        if ($this->selectedSiswaId && $this->showChart) {
            $selectedSiswa = Siswa::with('kelas.tahunAjar')->find($this->selectedSiswaId);

            if ($selectedSiswa) {
                $penilaianData = Penilaian::where('siswa_id', $this->selectedSiswaId)
                    ->orderBy('pertemuan')
                    ->get()
                    ->keyBy(fn ($p) => (int) $p->pertemuan);

                $labels = [];
                $values = [];
                for ($i = 1; $i <= 16; $i++) {
                    $labels[] = 'P' . $i;
                    $values[] = isset($penilaianData[$i]) ? (int) $penilaianData[$i]->level : null;
                }

                $pertemuanDinilai = $penilaianData->count();
                $totalLevel       = $penilaianData->sum(fn ($p) => (int) $p->level);
                $rataLaporan      = $pertemuanDinilai > 0
                    ? round($totalLevel / $pertemuanDinilai, 2)
                    : null;

                $chartData = [
                    'labels'           => $labels,
                    'values'           => $values,
                    'nama'             => $selectedSiswa->nama,
                    'kelas'            => $selectedSiswa->kelas?->nama,
                    'tahun_ajar'       => $selectedSiswa->kelas?->tahunAjar?->nama,
                    'rata_laporan'     => $rataLaporan,
                    'pertemuan_dinilai'=> $pertemuanDinilai,
                ];
            }
        }

        return view('livewire.laporan.laporan-individu-page', [
            'tahunAjarOptions' => $tahunAjarOptions,
            'kelasOptions'     => $kelasOptions,
            'siswaList'        => $siswaList,
            'chartData'        => $chartData,
            'selectedSiswa'    => $selectedSiswa,
            'sekolah'          => $sekolah,
        ]);
    }
}
