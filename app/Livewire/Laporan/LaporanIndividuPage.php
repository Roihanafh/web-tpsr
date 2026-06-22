<?php

namespace App\Livewire\Laporan;

use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporanIndividuPage extends Component
{
    public mixed  $kelasId         = 'all';
    public string $search          = '';
    public ?int   $selectedSiswaId = null;
    public bool   $showChart       = false;

    public ?int   $catatanSiswaId  = null;
    public string $catatanNama     = '';
    public string $catatanText     = '';
    public string $rekomendasiText = '';

    public function updatedKelasId(): void
    {
        $this->selectedSiswaId = null;
        $this->showChart       = false;
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

        $kelasOptions = $sekolah
            ? $sekolah->kelas()->orderBy('nama')->get()
            : collect();

        $siswaList = collect();
        if ($sekolah && $this->kelasId) {
            $siswaList = Siswa::with('kelas')
                ->when($this->kelasId === 'all', function ($q) use ($sekolah) {
                    $q->whereHas('kelas', fn ($qk) => $qk->where('sekolah_id', $sekolah->id));
                }, fn ($q) => $q->where('kelas_id', $this->kelasId))
                ->when(trim($this->search) !== '', fn ($q) => $q->where('nama', 'like', '%' . trim($this->search) . '%'))
                ->orderBy('nama')
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
                });
        }

        $chartData = $pdfData = $selectedSiswa = null;

        if ($this->selectedSiswaId && $this->showChart) {
            $selectedSiswa = Siswa::with('kelas')->find($this->selectedSiswaId);

            if ($selectedSiswa) {
                $penilaianList = Penilaian::where('siswa_id', $this->selectedSiswaId)
                    ->orderBy('pertemuan')->get()
                    ->keyBy(fn ($p) => (int) $p->pertemuan);

                $labels = $values = [];
                for ($i = 1; $i <= 16; $i++) {
                    $labels[] = 'P' . $i;
                    if (isset($penilaianList[$i])) {
                        [$s, $c] = $this->sumLevelRow($penilaianList[$i]);
                        $values[] = $c > 0 ? round($s / $c, 2) : null;
                    } else {
                        $values[] = null;
                    }
                }

                [$totalAll, $countAll] = $this->sumLevels($penilaianList);
                $rataLaporan = $countAll > 0 ? round($totalAll / $countAll, 2) : null;

                $chartData = [
                    'labels'            => $labels,
                    'values'            => $values,
                    'nama'              => $selectedSiswa->nama,
                    'kelas'             => $selectedSiswa->kelas?->nama,
                    'tahun_ajar'        => $selectedSiswa->kelas?->nama ?? '-',
                    'rata_laporan'      => $rataLaporan,
                    'pertemuan_dinilai' => $penilaianList->count(),
                ];

                $pdfData = [
                    'siswa'       => $selectedSiswa,
                    'pengajar'    => Auth::user()?->name ?? '-',
                    'sekolahNama' => $sekolah?->nama ?? '-',
                    'rataLaporan' => $rataLaporan ?? 0,
                    'status'      => self::getStatus($rataLaporan),
                    'semester'    => $selectedSiswa->kelas?->nama ?? '-',
                ];
            }
        }

        return view('livewire.laporan.laporan-individu-page', compact(
            'kelasOptions', 'siswaList', 'chartData', 'pdfData', 'selectedSiswa', 'sekolah'
        ));
    }

    private function sumLevels($collection): array
    {
        $total = $count = 0;
        foreach ($collection as $p) {
            [$s, $c] = $this->sumLevelRow($p);
            $total += $s; $count += $c;
        }
        return [$total, $count];
    }

    private function sumLevelRow($p): array
    {
        $sum = $cnt = 0;
        foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $l) {
            if ($p->{$l} !== null) { $sum += (int) $p->{$l}; $cnt++; }
        }
        return [$sum, $cnt];
    }

    public function openCatatan(int $siswaId): void
    {
        $siswa = Siswa::findOrFail($siswaId);
        
        $all = Penilaian::where('siswa_id', $siswaId)->get();
        if ($all->count() < 16) {
            session()->flash('error', 'Catatan hanya bisa diisi jika siswa sudah dinilai untuk semua (16) pertemuan.');
            return;
        }

        $this->catatanSiswaId  = $siswa->id;
        $this->catatanNama     = $siswa->nama;
        $this->catatanText     = $siswa->keterangan ?? '';
        $this->rekomendasiText = $siswa->rekomendasi ?? '';

        $this->dispatch('open-catatan-modal');
    }

    public function saveCatatan(): void
    {
        $this->validate([
            'catatanText'     => ['nullable', 'string', 'max:500'],
            'rekomendasiText' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->catatanSiswaId) {
            Siswa::where('id', $this->catatanSiswaId)->update([
                'keterangan'  => $this->catatanText ?: null,
                'rekomendasi' => $this->rekomendasiText ?: null,
            ]);

            session()->flash('success', 'Catatan & Rekomendasi siswa ' . $this->catatanNama . ' berhasil disimpan.');
            $this->dispatch('close-catatan-modal');
            
            // Refresh render
            $this->render();
        }
    }
}
