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
    public array   $importResult   = [];   // [{nama, pertemuan, action}]

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
        // Default: semua 4
        foreach ($students as $s) {
            $this->ratings[$s->id] = ['L0' => 4, 'L1' => 4, 'L2' => 4, 'L3' => 4, 'L4' => 4];
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
            $this->ratings[$s->id] = ['L0' => 4, 'L1' => 4, 'L2' => 4, 'L3' => 4, 'L4' => 4];
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
            $this->fileImport    = null;
            $this->importFailures = [];
            $this->importResult   = [];
        }
    }

    public function import(): void
    {
        if (! Auth::user()?->can('view_assessment')) abort(403);

        $sekolah = Auth::user()?->sekolah;
        if (! $sekolah) {
            session()->flash('error', 'Akun belum terhubung dengan sekolah.');
            return;
        }

        $this->validate([
            'fileImport' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'fileImport.required' => 'File wajib dipilih.',
            'fileImport.mimes'    => 'Format file harus XLS, XLSX, atau CSV.',
        ]);

        $path = $this->fileImport->getRealPath();

        $importer = new \App\Imports\TpsrTemplateImport($sekolah);

        try {
            $importer->import($path);
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses file: ' . $e->getMessage());
            return;
        }

        // Simpan hasil ke property untuk ditampilkan di view
        $this->importResult   = $importer->imported;
        $this->importFailures = array_merge(
            array_map(fn ($w) => ['line' => '-', 'message' => '⚠️ ' . $w], $importer->warnings),
            array_map(fn ($s) => ['line' => $s['nama'], 'message' => $s['reason']], $importer->skipped),
        );

        $importCount = count($importer->imported);

        if ($importCount > 0) {
            // Reload ke kelas yang diimport dan langsung tampilkan pertemuan terakhir
            if ($importer->kelasNama) {
                $matchKelas = \App\Models\Kelas::where('sekolah_id', $sekolah->id)
                    ->where('nama', $importer->kelasNama)->first();
                if ($matchKelas) {
                    $this->kelasId   = $matchKelas->id;
                    $this->pertemuan = $importer->lastPertemuan !== null
                        ? (string) $importer->lastPertemuan
                        : null;
                    $this->ratings   = [];
                    $this->renderKey++;
                    $this->loadPenilaian();
                }
            }

            $kelasInfo = $importer->kelasIsNew
                ? "kelas {$importer->kelasNama} (baru dibuat)"
                : "kelas {$importer->kelasNama}";

            session()->flash('success',
                "{$importCount} siswa berhasil diimport ke {$kelasInfo}."
            );
        } else {
            session()->flash('error', 'Tidak ada data siswa yang berhasil diimport.');
        }

        $this->fileImport     = null;
        $this->showImportForm = false;
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            readfile(public_path('TPSR_template.xlsx'));
        }, 'TPSR_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
