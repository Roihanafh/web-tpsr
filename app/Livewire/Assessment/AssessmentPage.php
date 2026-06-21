<?php

namespace App\Livewire\Assessment;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AssessmentPage extends Component
{
    public ?bool $isGanjil = null;   // null = belum pilih, true = Ganjil, false = Genap

    public ?int $kelasId = null;

    public ?string $pertemuan = null;

    public array $ratings = [];

    public bool $isAssessed = false;

    public function mount(): void
    {
        if (! Auth::user()?->can('view_assessment')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function render(): View
    {
        $sekolah = Auth::user()?->sekolah;

        $kelasOptions = collect();
        if ($sekolah && $this->isGanjil !== null) {
            $kelasOptions = $sekolah->kelas()
                ->where('is_ganjil', $this->isGanjil)
                ->orderBy('nama')
                ->get();
        }

        $students = collect();
        if ($this->kelasId) {
            $students = Siswa::where('kelas_id', $this->kelasId)
                ->orderBy('nama')
                ->get();
        }

        return view('livewire.assessment.assessment-page', [
            'semesterOptions' => [
                ['value' => '1', 'label' => 'Semester Ganjil'],
                ['value' => '0', 'label' => 'Semester Genap'],
            ],
            'kelasOptions' => $kelasOptions,
            'students'     => $students,
        ]);
    }

    public function updatedIsGanjil(): void
    {
        $this->kelasId  = null;
        $this->ratings  = [];
        $this->isAssessed = false;
        $this->resetValidation();
    }

    public function updatedKelasId(): void
    {
        $this->ratings  = [];
        $this->isAssessed = false;
        $this->resetValidation();
        $this->loadPenilaian();
    }

    public function updatedPertemuan(): void
    {
        $this->ratings  = [];
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

        // Default: semua 5
        foreach ($students as $student) {
            $this->ratings[$student->id] = [
                'L0' => 5, 'L1' => 5, 'L2' => 5, 'L3' => 5, 'L4' => 5,
            ];
        }

        $siswaIds = $students->pluck('id');
        $existingPenilaian = Penilaian::whereIn('siswa_id', $siswaIds)
            ->where('pertemuan', $this->pertemuan)
            ->get();

        if ($existingPenilaian->isNotEmpty()) {
            $this->isAssessed = true;
            foreach ($existingPenilaian as $p) {
                $this->ratings[$p->siswa_id] = [
                    'L0' => $p->L0,
                    'L1' => $p->L1,
                    'L2' => $p->L2,
                    'L3' => $p->L3,
                    'L4' => $p->L4,
                ];
            }
        }
    }

    public function kosongkanPenilaian(): void
    {
        if (! $this->kelasId || ! $this->pertemuan) {
            return;
        }

        $students    = Siswa::where('kelas_id', $this->kelasId)->get();
        $studentIds  = $students->pluck('id');

        Penilaian::whereIn('siswa_id', $studentIds)
            ->where('pertemuan', $this->pertemuan)
            ->delete();

        foreach ($students as $student) {
            $this->recalcRataPoin($student);
            $this->ratings[$student->id] = [
                'L0' => 5, 'L1' => 5, 'L2' => 5, 'L3' => 5, 'L4' => 5,
            ];
        }

        $this->isAssessed = false;
        session()->flash('success', 'Penilaian pertemuan ' . $this->pertemuan . ' berhasil dikosongkan.');
        $this->resetValidation();
    }

    public function save(): void
    {
        if (! Auth::user()?->can('view_assessment')) {
            abort(403);
        }

        $sekolah = Auth::user()?->sekolah;
        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');
            return;
        }

        $this->validate([
            'isGanjil'  => ['required', 'boolean'],
            'kelasId'   => ['required', 'exists:kelas,id'],
            'pertemuan' => ['required', 'integer', 'between:1,16'],
        ], [
            'isGanjil.required'  => 'Semester wajib dipilih.',
            'kelasId.required'   => 'Kelas wajib dipilih.',
            'pertemuan.required' => 'Pertemuan wajib dipilih.',
        ]);

        $students = Siswa::where('kelas_id', $this->kelasId)->get();

        foreach ($students as $student) {
            $r = $this->ratings[$student->id] ?? [];

            $hasValue = collect($r)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();

            if (! $hasValue) {
                Penilaian::where('siswa_id', $student->id)
                    ->where('pertemuan', $this->pertemuan)
                    ->delete();
            } else {
                Penilaian::updateOrCreate(
                    ['siswa_id' => $student->id, 'pertemuan' => $this->pertemuan],
                    [
                        'L0' => isset($r['L0']) && $r['L0'] !== '' ? (string) $r['L0'] : null,
                        'L1' => isset($r['L1']) && $r['L1'] !== '' ? (string) $r['L1'] : null,
                        'L2' => isset($r['L2']) && $r['L2'] !== '' ? (string) $r['L2'] : null,
                        'L3' => isset($r['L3']) && $r['L3'] !== '' ? (string) $r['L3'] : null,
                        'L4' => isset($r['L4']) && $r['L4'] !== '' ? (string) $r['L4'] : null,
                    ]
                );
            }

            $this->recalcRataPoin($student);
        }

        $siswaIds = $students->pluck('id');
        $this->isAssessed = Penilaian::whereIn('siswa_id', $siswaIds)
            ->where('pertemuan', $this->pertemuan)
            ->exists();

        session()->flash('success', 'Penilaian pertemuan ' . $this->pertemuan . ' berhasil disimpan.');
        $this->resetValidation();
    }

    private function recalcRataPoin(Siswa $student): void
    {
        $all = Penilaian::where('siswa_id', $student->id)->get();

        if ($all->isEmpty()) {
            $student->update(['rata_poin' => 0]);
            return;
        }

        $total  = 0;
        $count  = 0;
        $levels = ['L0', 'L1', 'L2', 'L3', 'L4'];

        foreach ($all as $p) {
            foreach ($levels as $l) {
                if ($p->{$l} !== null) {
                    $total += (int) $p->{$l};
                    $count++;
                }
            }
        }

        $student->update([
            'rata_poin' => $count > 0 ? round($total / $count, 2) : 0,
        ]);
    }
}
