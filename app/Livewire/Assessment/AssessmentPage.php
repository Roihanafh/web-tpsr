<?php

namespace App\Livewire\Assessment;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AssessmentPage extends Component
{
    public ?int $tahunAjarId = null;

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
        if ($sekolah && $this->tahunAjarId) {
            $kelasOptions = $sekolah->kelas()
                ->where('tahun_ajar_id', $this->tahunAjarId)
                ->orderBy('nama')
                ->get();
        }

        $students = collect();
        if ($sekolah && $this->kelasId) {
            $students = Siswa::where('kelas_id', $this->kelasId)
                ->orderBy('nama')
                ->get();
        }

        return view('livewire.assessment.assessment-page', [
            'tahunAjarOptions' => TahunAjar::getSorted(),
            'kelasOptions' => $kelasOptions,
            'students' => $students,
            'sekolah' => $sekolah,
        ]);
    }

    public function updatedTahunAjarId(): void
    {
        $sekolah = Auth::user()?->sekolah;
        $currentKelas = null;

        if ($this->kelasId) {
            $currentKelas = Kelas::find($this->kelasId);
        }

        $this->ratings = [];
        $this->resetValidation();

        if ($currentKelas && $sekolah && $this->tahunAjarId) {
            $newKelas = $sekolah->kelas()
                ->where('tahun_ajar_id', $this->tahunAjarId)
                ->where('nama', $currentKelas->nama)
                ->first();

            if ($newKelas) {
                $this->kelasId = $newKelas->id;
                if ($this->pertemuan) {
                    $this->loadPenilaian();
                }
                return;
            }
        }

        $this->kelasId = null;
    }

    public function updatedKelasId(): void
    {
        $this->ratings = [];
        $this->resetValidation();
        $this->loadPenilaian();
    }

    public function updatedPertemuan(): void
    {
        $this->ratings = [];
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

        foreach ($students as $student) {
            $this->ratings[$student->id] = '5';
        }

        $siswaIds = $students->pluck('id');
        $existingPenilaian = Penilaian::whereIn('siswa_id', $siswaIds)
            ->where('pertemuan', $this->pertemuan)
            ->get();

        if ($existingPenilaian->isNotEmpty()) {
            $this->isAssessed = true;
            foreach ($existingPenilaian as $p) {
                $this->ratings[$p->siswa_id] = (string) $p->level;
            }
        }
    }

    public function kosongkanPenilaian(): void
    {
        if (! $this->kelasId || ! $this->pertemuan) {
            return;
        }

        $students = Siswa::where('kelas_id', $this->kelasId)->get();
        $studentIds = $students->pluck('id');

        // Delete all penilaian records for these students at this meeting
        Penilaian::whereIn('siswa_id', $studentIds)
            ->where('pertemuan', $this->pertemuan)
            ->delete();

        // Recalculate average (rata_poin) for each student in the class
        foreach ($students as $student) {
            $allPenilaians = Penilaian::where('siswa_id', $student->id)->get();
            if ($allPenilaians->isNotEmpty()) {
                $totalLevel       = $allPenilaians->sum(fn ($p) => (int) $p->level);
                $pertemuanDinilai = $allPenilaians->count();
                $student->update([
                    'rata_poin' => round($totalLevel / $pertemuanDinilai, 2),
                ]);
            } else {
                $student->update(['rata_poin' => 0]);
            }
        }

        // Reset ratings form in UI
        foreach ($students as $student) {
            $this->ratings[$student->id] = '5';
        }

        // Set isAssessed to false
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
            'tahunAjarId' => ['required', 'exists:tahun_ajar,id'],
            'kelasId' => ['required', 'exists:kelas,id'],
            'pertemuan' => ['required', 'integer', 'between:1,16'],
        ], [
            'tahunAjarId.required' => 'Tahun ajaran wajib dipilih.',
            'kelasId.required' => 'Kelas wajib dipilih.',
            'pertemuan.required' => 'Pertemuan wajib dipilih.',
        ]);

        $students = Siswa::where('kelas_id', $this->kelasId)->get();

        // Save/delete records and recalculate average
        foreach ($students as $student) {
            $level = $this->ratings[$student->id] ?? null;

            if ($level === null || $level === '') {
                Penilaian::where('siswa_id', $student->id)
                    ->where('pertemuan', $this->pertemuan)
                    ->delete();
            } else {
                Penilaian::updateOrCreate(
                    [
                        'siswa_id' => $student->id,
                        'pertemuan' => $this->pertemuan,
                    ],
                    [
                        'level' => $level,
                    ]
                );
            }

            // Recalculate average (L0 = 0 to L5 = 5)
            // Formula: total level / jumlah pertemuan yang sudah dinilai
            $allPenilaians = Penilaian::where('siswa_id', $student->id)->get();
            if ($allPenilaians->isNotEmpty()) {
                $totalLevel       = $allPenilaians->sum(fn ($p) => (int) $p->level);
                $pertemuanDinilai = $allPenilaians->count();
                $student->update([
                    'rata_poin' => round($totalLevel / $pertemuanDinilai, 2),
                ]);
            } else {
                $student->update(['rata_poin' => 0]);
            }
        }

        $siswaIds = $students->pluck('id');
        $existingPenilaian = Penilaian::whereIn('siswa_id', $siswaIds)
            ->where('pertemuan', $this->pertemuan)
            ->exists();

        $this->isAssessed = $existingPenilaian;
        session()->flash('success', 'Penilaian pertemuan ' . $this->pertemuan . ' berhasil disimpan.');
        $this->resetValidation();
    }
}
