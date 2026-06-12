<?php

namespace App\Livewire\Laporan;

use App\Models\TahunAjar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporanKelasPage extends Component
{
    public ?int $tahunAjarId = null;

    public ?int $kelasId = null;

    public string $search = '';

    public function updatedTahunAjarId(): void
    {
        $this->kelasId = null;
    }

    public function render(): View
    {
        $sekolah = Auth::user()?->sekolah;

        $tahunAjarOptions = TahunAjar::orderByDesc('id')->get();

        $kelasOptions = collect();
        if ($sekolah && $this->tahunAjarId) {
            $kelasOptions = $sekolah->kelas()
                ->where('tahun_ajar_id', $this->tahunAjarId)
                ->orderBy('nama')
                ->get();
        }

        return view('livewire.laporan.laporan-kelas-page', [
            'tahunAjarOptions' => $tahunAjarOptions,
            'kelasOptions'     => $kelasOptions,
            'sekolah'          => $sekolah,
        ]);
    }
}
