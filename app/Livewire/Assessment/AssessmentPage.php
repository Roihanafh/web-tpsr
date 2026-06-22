<?php

namespace App\Livewire\Assessment;

use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class AssessmentPage extends Component
{
    use WithFileUploads;

    public ?int    $kelasId   = null;
    public ?string $pertemuan = null;
    public array   $ratings   = [];
    public bool    $isAssessed = false;
    public int     $renderKey  = 0;  // force re-render select setelah kosongkan
    public bool    $showImportForm = false;
    public mixed   $fileImport     = null;
    public array   $importFailures = [];

    public function mount(): void
    {
        if (! Auth::user()?->can('view_assessment')) {
            abort(403);
        }
    }

    public function render(): View
    {
        $sekolah = Auth::user()?->sekolah;

        $kelasOptions = $sekolah
            ? $sekolah->kelas()->orderBy('nama')->get()
            : collect();

        $students = $this->kelasId
            ? Siswa::where('kelas_id', $this->kelasId)->orderBy('nama')->get()
            : collect();

        return view('livewire.assessment.assessment-page', [
            'kelasOptions' => $kelasOptions,
            'students'     => $students,
            'renderKey'    => $this->renderKey,
        ]);
    }

    public function updatedKelasId(): void
    {
        $this->ratings   = [];
        $this->isAssessed = false;
        $this->resetValidation();
        $this->loadPenilaian();
    }

    public function updatedPertemuan(): void
    {
        $this->ratings   = [];
        $this->isAssessed = false;
        $this->resetValidation();
        $this->loadPenilaian();
    }

    public function loadPenilaian(): void
    {
        $this->isAssessed = false;

        if (! $this->kelasId || ! $this->pertemuan) {
            return;
        }

        $students = Siswa::where('kelas_id', $this->kelasId)->get();
        // Default: semua - (kosong)
        foreach ($students as $s) {
            $this->ratings[$s->id] = ['L0' => '', 'L1' => '', 'L2' => '', 'L3' => '', 'L4' => ''];
        }

        $existing = Penilaian::whereIn('siswa_id', $students->pluck('id'))
            ->where('pertemuan', $this->pertemuan)
            ->get();

        if ($existing->isNotEmpty()) {
            $this->isAssessed = true;
            foreach ($existing as $p) {
                $this->ratings[$p->siswa_id] = [
                    'L0' => $p->L0, 'L1' => $p->L1, 'L2' => $p->L2, 'L3' => $p->L3, 'L4' => $p->L4,
                ];
            }
        }
    }

    public function kosongkanPenilaian(): void
    {
        if (! $this->kelasId || ! $this->pertemuan) return;

        $students = Siswa::where('kelas_id', $this->kelasId)->get();

        Penilaian::whereIn('siswa_id', $students->pluck('id'))
            ->where('pertemuan', $this->pertemuan)
            ->delete();

        foreach ($students as $s) {
            $this->recalcRataPoin($s);
            $this->ratings[$s->id] = ['L0' => '', 'L1' => '', 'L2' => '', 'L3' => '', 'L4' => ''];
        }

        $this->isAssessed = false;
        $this->renderKey++;
        session()->flash('success', 'Penilaian pertemuan ' . $this->pertemuan . ' berhasil dikosongkan.');
        $this->resetValidation();
    }

    public function save(): void
    {
        if (! Auth::user()?->can('view_assessment')) abort(403);

        $sekolah = Auth::user()?->sekolah;
        if (! $sekolah) {
            session()->flash('error', 'Akun belum terhubung dengan sekolah.');
            return;
        }

        $this->validate([
            'kelasId'   => ['required', 'exists:kelas,id'],
            'pertemuan' => ['required', 'integer', 'between:1,16'],
        ]);

        $students = Siswa::where('kelas_id', $this->kelasId)->get();

        foreach ($students as $s) {
            $r = $this->ratings[$s->id] ?? [];
            $hasValue = collect($r)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();

            if (! $hasValue) {
                Penilaian::where('siswa_id', $s->id)->where('pertemuan', $this->pertemuan)->delete();
            } else {
                Penilaian::updateOrCreate(
                    ['siswa_id' => $s->id, 'pertemuan' => $this->pertemuan],
                    [
                        'L0' => $r['L0'] !== '' ? (string) $r['L0'] : null,
                        'L1' => $r['L1'] !== '' ? (string) $r['L1'] : null,
                        'L2' => $r['L2'] !== '' ? (string) $r['L2'] : null,
                        'L3' => $r['L3'] !== '' ? (string) $r['L3'] : null,
                        'L4' => $r['L4'] !== '' ? (string) $r['L4'] : null,
                    ]
                );
            }

            $this->recalcRataPoin($s);
        }

        $this->isAssessed = Penilaian::whereIn('siswa_id', $students->pluck('id'))
            ->where('pertemuan', $this->pertemuan)->exists();

        session()->flash('success', 'Penilaian pertemuan ' . $this->pertemuan . ' berhasil disimpan.');
        $this->resetValidation();
    }

    public function toggleImportForm(): void
    {
        $this->showImportForm = ! $this->showImportForm;
        if ($this->showImportForm) {
            $this->fileImport = null;
        }
    }

    public function import(): void
    {
        // Logika import dikosongkan terlebih dahulu sesuai request
    }

    public function downloadTemplate(): void
    {
        // Logika download template dikosongkan terlebih dahulu sesuai request
    }

    private function recalcRataPoin(Siswa $student): void
    {
        $all = Penilaian::where('siswa_id', $student->id)->get();
        if ($all->isEmpty()) { $student->update(['rata_poin' => 0]); return; }

        $total = $count = 0;
        foreach ($all as $p) {
            foreach (['L0', 'L1', 'L2', 'L3', 'L4'] as $l) {
                if ($p->{$l} !== null) { $total += (int) $p->{$l}; $count++; }
            }
        }
        $student->update(['rata_poin' => $count > 0 ? round($total / $count, 2) : 0]);
    }
}
