<?php

namespace App\Livewire\Kelas;

use App\Exports\KelasExport;
use App\Exports\KelasTemplateExport;
use App\Imports\KelasImport;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\TahunAjar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KelasPage extends Component
{
    use WithFileUploads;

    public ?string $tahunAjarYear = null;

    public ?int $kelasId = null;

    public ?int $tahunAjarId = null;

    public ?int $filterTahunAjarId = null;

    public string $search = '';

    public string $nama = '';

    public mixed $fileImport = null;

    public bool $isEditing = false;

    public bool $showForm = false;

    public bool $showImportForm = false;

    public array $importFailures = [];

    public function render(): View
    {
        $tahunAjarOptions = TahunAjar::getSorted();

        $uniqueYears = $tahunAjarOptions->map(function ($ta) {
            return trim(str_ireplace(['ganjil', 'genap'], '', $ta->nama));
        })->unique()->filter()->values()->toArray();

        return view('livewire.kelas.kelas-page', [
            'tahunAjarOptions' => $tahunAjarOptions,
            'uniqueYears' => $uniqueYears,
            'showForm' => $this->showForm,
            'showImportForm' => $this->showImportForm,
            'importFailures' => $this->importFailures,
            'filterTahunAjarId' => $this->filterTahunAjarId,
            'search' => $this->search,
            'sekolah' => $this->currentSekolah(),
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
                    'max:5',
                    Rule::unique('kelas', 'nama')
                        ->where('sekolah_id', $sekolah->id)
                        ->where('tahun_ajar_id', $this->tahunAjarId)
                        ->ignore($this->kelasId),
                ],
                'tahunAjarId' => ['required', 'exists:tahun_ajar,id'],
            ], [
                'nama.unique' => 'Kelas dengan nama dan tahun ajaran tersebut sudah ada.',
                'tahunAjarId.required' => 'Tahun ajaran wajib dipilih.',
            ]);

            Kelas::updateOrCreate(
                ['id' => $this->kelasId],
                [
                    'sekolah_id' => $sekolah->id,
                    'tahun_ajar_id' => $validated['tahunAjarId'],
                    'nama' => strtoupper($validated['nama']),
                ],
            );
        } else {
            $validated = $this->validate([
                'nama' => ['required', 'string', 'max:5'],
                'tahunAjarYear' => ['required', 'string'],
            ], [
                'nama.required' => 'Nama kelas wajib diisi.',
                'tahunAjarYear.required' => 'Tahun ajaran wajib dipilih.',
            ]);

            $year = $validated['tahunAjarYear'];
            $ganjil = TahunAjar::where('nama', $year . ' Ganjil')->first();
            $genap = TahunAjar::where('nama', $year . ' Genap')->first();

            if (! $ganjil || ! $genap) {
                $this->addError('tahunAjarYear', 'Tahun ajaran ganjil dan genap untuk ' . $year . ' tidak lengkap di database.');
                return;
            }

            // Check uniqueness in both semesters
            $existsGanjil = Kelas::where('sekolah_id', $sekolah->id)
                ->where('tahun_ajar_id', $ganjil->id)
                ->where('nama', strtoupper($validated['nama']))
                ->exists();

            $existsGenap = Kelas::where('sekolah_id', $sekolah->id)
                ->where('tahun_ajar_id', $genap->id)
                ->where('nama', strtoupper($validated['nama']))
                ->exists();

            if ($existsGanjil || $existsGenap) {
                $this->addError('nama', 'Kelas dengan nama dan tahun ajaran tersebut sudah ada.');
                return;
            }

            // Create both
            Kelas::create([
                'sekolah_id' => $sekolah->id,
                'tahun_ajar_id' => $ganjil->id,
                'nama' => strtoupper($validated['nama']),
            ]);

            Kelas::create([
                'sekolah_id' => $sekolah->id,
                'tahun_ajar_id' => $genap->id,
                'nama' => strtoupper($validated['nama']),
            ]);
        }

        session()->flash('success', $this->isEditing ? 'Data kelas berhasil diperbarui.' : 'Data kelas berhasil ditambahkan.');

        $this->resetForm();
        $this->dispatch('refreshDatatable');
    }

    #[On('edit-kelas')]
    public function edit(int $id): void
    {
        $kelas = $this->baseKelasQuery()->findOrFail($id);

        $this->kelasId = $kelas->id;
        $this->tahunAjarId = $kelas->tahun_ajar_id;
        $this->nama = $kelas->nama;
        $this->isEditing = true;
        $this->showForm = true;
        $this->showImportForm = false;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    #[On('delete-kelas')]
    public function delete(int $id): void
    {
        $sekolah = $this->currentSekolah();
        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');
            return;
        }

        $kelas = $this->baseKelasQuery()->with('tahunAjar')->find($id);
        if (! $kelas) {
            session()->flash('error', 'Data kelas tidak ditemukan.');
            return;
        }

        $year = trim(str_ireplace(['ganjil', 'genap'], '', $kelas->tahunAjar->nama));
        $ganjilTa = TahunAjar::where('nama', $year . ' Ganjil')->first();
        $genapTa = TahunAjar::where('nama', $year . ' Genap')->first();

        $tahunAjarIds = [];
        if ($ganjilTa) $tahunAjarIds[] = $ganjilTa->id;
        if ($genapTa) $tahunAjarIds[] = $genapTa->id;

        $kelasToDelete = Kelas::where('sekolah_id', $sekolah->id)
            ->where('nama', $kelas->nama)
            ->whereIn('tahun_ajar_id', $tahunAjarIds)
            ->get();

        $count = 0;
        foreach ($kelasToDelete as $k) {
            $k->delete();
            $count++;
        }

        session()->flash('success', "Data kelas {$kelas->nama} berhasil dihapus dari {$count} semester.");
        $this->dispatch('refreshDatatable');
    }

    public function updatedFilterTahunAjarId(): void
    {
        $this->dispatch('kelas-filter-changed', tahunAjarId: $this->filterTahunAjarId);
    }

    public function updatedSearch(): void
    {
        $this->dispatch('kelas-search-changed', search: trim($this->search));
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->showImportForm = false;
        if ($this->showForm) {
            $this->reset(['kelasId', 'nama', 'isEditing']);
            $this->resetValidation();
            $this->tahunAjarYear = null;
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

        $import = new KelasImport($sekolah, $this->tahunAjarYear, $ganjil, $genap);
        Excel::import($import, $this->fileImport);

        $this->importFailures = $import->failures();

        if ($import->insertedCount() > 0) {
            session()->flash('success', $import->insertedCount().' data kelas berhasil diimport.');
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

        $tahunAjarPart = 'semua tahun ajar';
        if ($this->filterTahunAjarId) {
            $ta = TahunAjar::find($this->filterTahunAjarId);
            if ($ta) {
                $tahunAjarPart = 'tahun ajar ' . str_replace('/', '-', $ta->nama);
            }
        }

        $filename = "data kelas {$tahunAjarPart}.xlsx";

        return Excel::download(
            new KelasExport($sekolah, $this->filterTahunAjarId ?: null),
            $filename,
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new KelasTemplateExport(), 'template-import-kelas.xlsx');
    }

    private function resetForm(): void
    {
        $this->reset(['kelasId', 'nama', 'isEditing']);
        $this->resetValidation();
        $this->showForm = false;
    }

    private function currentSekolah(): ?Sekolah
    {
        return Auth::user()?->sekolah;
    }

    private function baseKelasQuery()
    {
        $sekolah = $this->currentSekolah();

        return Kelas::query()->where('sekolah_id', $sekolah?->id);
    }
}
