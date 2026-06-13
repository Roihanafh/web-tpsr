<?php

namespace App\Livewire\Siswa;

use App\Exports\SiswaExport;
use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\TahunAjar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SiswaPage extends Component
{
    use WithFileUploads;

    public ?string $tahunAjarYear = null;

    public ?string $kelasNama = null;

    public ?int $siswaId = null;

    public ?int $kelasId = null;

    public ?int $tahunAjarId = null;

    public string $filterKelasNama = '';

    public ?int $filterTahunAjarId = null;

    public string $search = '';

    public string $nama = '';

    public string $gender = '';

    public string $tambahMode = '2';

    public string $selectedSemester = 'ganjil';

    public mixed $fileImport = null;

    public bool $isEditing = false;

    public bool $showForm = false;

    public bool $showImportForm = false;

    public array $importFailures = [];

    public function render(): View
    {
        $sekolah = $this->currentSekolah();
        $tahunAjarOptions = TahunAjar::getSorted();

        $uniqueYears = $tahunAjarOptions->map(function ($ta) {
            return trim(str_ireplace(['ganjil', 'genap'], '', $ta->nama));
        })->unique()->filter()->values()->toArray();

        $uniqueKelasOptions = collect();
        if ($this->tahunAjarYear && $sekolah) {
            $uniqueKelasOptions = $sekolah->kelas()
                ->whereHas('tahunAjar', function ($query) {
                    $query->where('nama', 'like', $this->tahunAjarYear . ' %');
                })
                ->select('nama')
                ->distinct()
                ->orderBy('nama')
                ->pluck('nama');
        }

        return view('livewire.siswa.siswa-page', [
            'kelasOptions' => $sekolah
                ? $sekolah->kelas()
                    ->when($this->tahunAjarId, fn ($query) => $query->where('tahun_ajar_id', $this->tahunAjarId))
                    ->orderBy('nama')
                    ->get()
                : collect(),
            'uniqueKelasOptions' => $uniqueKelasOptions,
            'filterKelasOptions' => $sekolah
                ? $sekolah->kelas()
                    ->select('nama')
                    ->distinct()
                    ->orderBy('nama')
                    ->pluck('nama')
                : collect(),
            'tahunAjarOptions' => $tahunAjarOptions,
            'uniqueYears' => $uniqueYears,
            'tahunAjarId' => $this->tahunAjarId,
            'filterKelasNama' => $this->filterKelasNama,
            'filterTahunAjarId' => $this->filterTahunAjarId,
            'search' => $this->search,
            'siswaId' => $this->siswaId,
            'showForm' => $this->showForm,
            'showImportForm' => $this->showImportForm,
            'importFailures' => $this->importFailures,
            'sekolah' => $sekolah,
        ]);
    }

    public function save(): void
    {
        $sekolah = $this->currentSekolah();

        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');

            return;
        }

        if ($this->isEditing) {
            $validated = $this->validate([
                'nama' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('siswa', 'nama')
                        ->where('kelas_id', $this->kelasId)
                        ->ignore($this->siswaId),
                ],
                'gender' => ['required', 'in:L,P'],
                'tahunAjarId' => ['required', 'exists:tahun_ajar,id'],
                'kelasId' => ['required', 'exists:kelas,id'],
            ], [
                'nama.unique' => 'Nama siswa dengan kelas tersebut sudah ada.',
                'tahunAjarId.required' => 'Tahun ajaran wajib dipilih.',
                'kelasId.required' => 'Kelas wajib dipilih.',
                'gender.required' => 'Jenis kelamin wajib dipilih.',
            ]);

            $kelasExistsForTahunAjar = $sekolah->kelas()
                ->whereKey($validated['kelasId'])
                ->where('tahun_ajar_id', $validated['tahunAjarId'])
                ->exists();

            if (! $kelasExistsForTahunAjar) {
                $this->addError('kelasId', 'Kelas yang dipilih tidak sesuai dengan tahun ajaran.');

                return;
            }

            Siswa::updateOrCreate(
                ['id' => $this->siswaId],
                [
                    'kelas_id' => $validated['kelasId'],
                    'nama' => $validated['nama'],
                    'gender' => $validated['gender'],
                    'rata_poin' => 0,
                ],
            );
        } else {
            $validated = $this->validate([
                'nama' => ['required', 'string', 'max:100'],
                'gender' => ['required', 'in:L,P'],
                'tahunAjarYear' => ['required', 'string'],
                'kelasNama' => ['required', 'string'],
                'tambahMode' => ['required', 'in:1,2'],
                'selectedSemester' => ['required_if:tambahMode,1', 'in:ganjil,genap'],
            ], [
                'nama.required' => 'Nama siswa wajib diisi.',
                'tahunAjarYear.required' => 'Tahun ajaran wajib dipilih.',
                'kelasNama.required' => 'Kelas wajib dipilih.',
                'gender.required' => 'Jenis kelamin wajib dipilih.',
            ]);

            $year = $validated['tahunAjarYear'];

            if ($this->tambahMode === '2') {
                $ganjil = TahunAjar::where('nama', $year . ' Ganjil')->first();
                $genap = TahunAjar::where('nama', $year . ' Genap')->first();

                if (! $ganjil || ! $genap) {
                    $this->addError('tahunAjarYear', 'Tahun ajaran ganjil dan genap untuk ' . $year . ' tidak lengkap di database.');
                    return;
                }

                $ganjilKelas = $sekolah->kelas()
                    ->where('nama', $validated['kelasNama'])
                    ->where('tahun_ajar_id', $ganjil->id)
                    ->first();

                $genapKelas = $sekolah->kelas()
                    ->where('nama', $validated['kelasNama'])
                    ->where('tahun_ajar_id', $genap->id)
                    ->first();

                if (! $ganjilKelas || ! $genapKelas) {
                    $this->addError('kelasNama', 'Kelas ' . $validated['kelasNama'] . ' tidak lengkap untuk semester ganjil dan genap.');
                    return;
                }

                // Check duplicate in both semesters
                $existsGanjil = Siswa::where('kelas_id', $ganjilKelas->id)
                    ->where('nama', $validated['nama'])
                    ->exists();

                $existsGenap = Siswa::where('kelas_id', $genapKelas->id)
                    ->where('nama', $validated['nama'])
                    ->exists();

                if ($existsGanjil || $existsGenap) {
                    $this->addError('nama', 'Nama siswa dengan kelas tersebut sudah ada.');
                    return;
                }

                // Create both
                Siswa::create([
                    'kelas_id' => $ganjilKelas->id,
                    'nama' => $validated['nama'],
                    'gender' => $validated['gender'],
                    'rata_poin' => 0,
                ]);

                Siswa::create([
                    'kelas_id' => $genapKelas->id,
                    'nama' => $validated['nama'],
                    'gender' => $validated['gender'],
                    'rata_poin' => 0,
                ]);
            } else {
                // tambahMode === '1'
                $semName = $this->selectedSemester === 'ganjil' ? 'Ganjil' : 'Genap';
                $taModel = TahunAjar::where('nama', $year . ' ' . $semName)->first();

                if (! $taModel) {
                    $this->addError('tahunAjarYear', 'Tahun ajaran ' . $year . ' ' . $semName . ' tidak ditemukan di database.');
                    return;
                }

                $targetKelas = $sekolah->kelas()
                    ->where('nama', $validated['kelasNama'])
                    ->where('tahun_ajar_id', $taModel->id)
                    ->first();

                if (! $targetKelas) {
                    $this->addError('kelasNama', 'Kelas ' . $validated['kelasNama'] . ' tidak ditemukan untuk semester ' . $semName . '.');
                    return;
                }

                $exists = Siswa::where('kelas_id', $targetKelas->id)
                    ->where('nama', $validated['nama'])
                    ->exists();

                if ($exists) {
                    $this->addError('nama', 'Nama siswa dengan kelas tersebut sudah ada.');
                    return;
                }

                Siswa::create([
                    'kelas_id' => $targetKelas->id,
                    'nama' => $validated['nama'],
                    'gender' => $validated['gender'],
                    'rata_poin' => 0,
                ]);
            }
        }

        session()->flash('success', $this->isEditing ? 'Data siswa berhasil diperbarui.' : 'Data siswa berhasil ditambahkan.');

        $this->resetForm();
        $this->dispatch('refreshDatatable');
    }

    #[On('edit-siswa')]
    public function edit(int $id): void
    {
        $siswa = $this->baseSiswaQuery()->with('kelas')->findOrFail($id);

        $this->siswaId = $siswa->id;
        $this->kelasId = $siswa->kelas_id;
        $this->tahunAjarId = $siswa->kelas?->tahun_ajar_id;
        $this->nama = $siswa->nama;
        $this->gender = $this->normalizeGender($siswa->gender);
        $this->isEditing = true;
        $this->showForm = true;
        $this->showImportForm = false;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    #[On('delete-siswa')]
    public function delete(int $id, string $option): void
    {
        $sekolah = $this->currentSekolah();
        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');
            return;
        }

        $siswa = $this->baseSiswaQuery()->find($id);
        if (! $siswa) {
            session()->flash('error', 'Data siswa tidak ditemukan.');
            return;
        }

        if ($option === 'all') {
            // Delete in all classes/semesters in the same school with same name & gender
            $allSiswa = Siswa::where('nama', $siswa->nama)
                ->where('gender', $siswa->gender)
                ->whereHas('kelas', function ($query) use ($sekolah) {
                    $query->where('sekolah_id', $sekolah->id);
                })
                ->get();

            $count = 0;
            foreach ($allSiswa as $s) {
                $s->delete();
                $count++;
            }

            session()->flash('success', "Data siswa {$siswa->nama} berhasil dihapus dari {$count} kelas.");
        } else {
            // Delete only for the selected semester/class
            $siswa->delete();
            session()->flash('success', "Data siswa {$siswa->nama} berhasil dihapus dari kelas saat ini.");
        }

        $this->dispatch('siswa-deleted');
        $this->dispatch('refreshDatatable');
    }

    public function updatedFilterKelasNama(): void
    {
        $kelasNama = ($this->filterKelasNama === '0' || $this->filterKelasNama === '') ? '' : $this->filterKelasNama;
        $this->dispatch('siswa-filter-changed', kelasNama: $kelasNama, tahunAjarId: $this->filterTahunAjarId);
    }

    public function updatedFilterTahunAjarId(): void
    {
        $kelasNama = ($this->filterKelasNama === '0' || $this->filterKelasNama === '') ? '' : $this->filterKelasNama;
        $this->dispatch('siswa-filter-changed', kelasNama: $kelasNama, tahunAjarId: $this->filterTahunAjarId);
    }

    public function updatedSearch(): void
    {
        $this->dispatch('siswa-search-changed', search: trim($this->search));
    }

    public function updatedTahunAjarId(): void
    {
        $this->kelasId = null;
    }

    public function updatedTahunAjarYear(): void
    {
        $this->kelasNama = null;
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->showImportForm = false;
        if ($this->showForm) {
            $this->reset(['siswaId', 'kelasId', 'tahunAjarId', 'nama', 'gender', 'isEditing']);
            $this->resetValidation();
            $this->tahunAjarYear = null;
            $this->kelasNama = null;
            $this->tambahMode = '2';
            $this->selectedSemester = 'ganjil';
        }
    }

    public function toggleImportForm(): void
    {
        $this->showImportForm = ! $this->showImportForm;
        $this->showForm = false;
        if ($this->showImportForm) {
            $this->tahunAjarYear = null;
            $this->fileImport = null;
        }
    }

    public function import(): void
    {
        $sekolah = $this->currentSekolah();

        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');

            return;
        }

        $this->validate([
            'tahunAjarYear' => ['required', 'string'],
            'fileImport' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'tahunAjarYear.required' => 'Pilih tahun ajaran sebelum import.',
            'fileImport.required' => 'Pilih file Excel yang akan diimport.',
        ]);

        $ganjil = TahunAjar::where('nama', $this->tahunAjarYear . ' Ganjil')->first();
        $genap = TahunAjar::where('nama', $this->tahunAjarYear . ' Genap')->first();

        if (! $ganjil || ! $genap) {
            $this->addError('tahunAjarYear', 'Tahun ajaran ganjil dan genap untuk ' . $this->tahunAjarYear . ' tidak lengkap di database.');
            return;
        }

        $import = new SiswaImport($sekolah, $this->tahunAjarYear, $ganjil, $genap);
        Excel::import($import, $this->fileImport);

        $this->importFailures = $import->failures();

        if ($import->insertedCount() > 0) {
            session()->flash('success', $import->insertedCount().' data siswa berhasil diimport.');
        }

        if ($this->importFailures !== []) {
            session()->flash('warning', count($this->importFailures).' baris gagal diimport. Lihat keterangan di bawah form.');
        }

        $this->fileImport = null;
        $this->showImportForm = false;
        $this->dispatch('refreshDatatable');
    }

    public function export(): BinaryFileResponse
    {
        $sekolah = $this->currentSekolah();

        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');
            $this->skipRender();
        }

        $kelasPart = 'semua kelas';
        if ($this->filterKelasNama !== '' && $this->filterKelasNama !== '0') {
            $kelasPart = 'kelas ' . $this->filterKelasNama;
        }

        $tahunAjarPart = 'semua tahun ajar';
        if ($this->filterTahunAjarId) {
            $ta = TahunAjar::find($this->filterTahunAjarId);
            if ($ta) {
                $tahunAjarPart = 'tahun ajar ' . str_replace('/', '-', $ta->nama);
            }
        }

        $filename = "data siswa {$kelasPart} {$tahunAjarPart}.xlsx";

        return Excel::download(
            new SiswaExport($sekolah, $this->filterKelasNama !== '' ? $this->filterKelasNama : null, $this->filterTahunAjarId ?: null),
            $filename,
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new SiswaTemplateExport(), 'template-import-siswa.xlsx');
    }

    private function resetForm(): void
    {
        $this->reset(['siswaId', 'kelasId', 'tahunAjarId', 'nama', 'gender', 'isEditing', 'tambahMode', 'selectedSemester']);
        $this->resetValidation();
        $this->showForm = false;
    }

    private function currentSekolah(): ?Sekolah
    {
        return Auth::user()?->sekolah;
    }

    private function normalizeGender(?string $gender): string
    {
        $gender = strtoupper(trim((string) $gender));

        return match ($gender) {
            'L', 'LAKI', 'LAKI-LAKI' => 'L',
            'P', 'PEREMPUAN' => 'P',
            default => '',
        };
    }

    private function baseSiswaQuery()
    {
        $sekolah = $this->currentSekolah();

        return Siswa::query()
            ->whereHas('kelas', fn ($query) => $query->where('sekolah_id', $sekolah?->id));
    }
}
