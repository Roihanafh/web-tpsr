<?php

namespace App\Livewire\Siswa;

use App\Exports\SiswaExport;
use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Siswa;
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

    // Form fields
    public ?int    $siswaId   = null;
    public ?int    $kelasId   = null;
    public string  $nama      = '';

    // Filter
    public string  $filterKelasNama  = '';
    public ?string $filterIsGanjil   = null;   // '1', '0', atau null
    public string  $search           = '';

    // Import
    public mixed $fileImport    = null;
    public array $importFailures = [];

    // UI flags
    public bool $isEditing      = false;
    public bool $showForm       = false;
    public bool $showImportForm = false;

    public function render(): View
    {
        $sekolah = $this->currentSekolah();

        // Distinct kelas names for filter dropdown
        $filterKelasOptions = $sekolah
            ? $sekolah->kelas()->select('nama')->distinct()->orderBy('nama')->pluck('nama')
            : collect();

        // Kelas options for form (edit mode)
        $kelasOptions = collect();
        if ($sekolah && $this->isEditing) {
            $kelasOptions = $sekolah->kelas()->orderBy('nama')->get();
        }

        return view('livewire.siswa.siswa-page', [
            'sekolah'           => $sekolah,
            'filterKelasOptions' => $filterKelasOptions,
            'kelasOptions'      => $kelasOptions,
            'filterKelasNama'   => $this->filterKelasNama,
            'filterIsGanjil'    => $this->filterIsGanjil,
            'search'            => $this->search,
            'showForm'          => $this->showForm,
            'showImportForm'    => $this->showImportForm,
            'importFailures'    => $this->importFailures,
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
                'nama'    => ['required', 'string', 'max:100',
                    Rule::unique('siswa', 'nama')->where('kelas_id', $this->kelasId)->ignore($this->siswaId),
                ],
                'kelasId' => ['required', 'exists:kelas,id'],
            ], [
                'nama.unique'    => 'Nama siswa dengan kelas tersebut sudah ada.',
                'kelasId.required' => 'Kelas wajib dipilih.',
            ]);

            Siswa::where('id', $this->siswaId)->update([
                'kelas_id' => $validated['kelasId'],
                'nama'     => $validated['nama'],
            ]);
        } else {
            $validated = $this->validate([
                'nama'    => ['required', 'string', 'max:100'],
                'kelasId' => ['required', 'exists:kelas,id'],
            ], [
                'nama.required'    => 'Nama siswa wajib diisi.',
                'kelasId.required' => 'Kelas wajib dipilih.',
            ]);

            // Cek apakah sekolah memiliki kelas tsb
            $kelasExists = $sekolah->kelas()->whereKey($validated['kelasId'])->exists();
            if (! $kelasExists) {
                $this->addError('kelasId', 'Kelas tidak ditemukan di sekolah ini.');
                return;
            }

            $exists = Siswa::where('kelas_id', $validated['kelasId'])
                ->where('nama', $validated['nama'])
                ->exists();

            if ($exists) {
                $this->addError('nama', 'Nama siswa dengan kelas tersebut sudah ada.');
                return;
            }

            Siswa::create([
                'kelas_id'  => $validated['kelasId'],
                'nama'      => $validated['nama'],
                'rata_poin' => 0,
            ]);
        }

        session()->flash('success', $this->isEditing ? 'Data siswa berhasil diperbarui.' : 'Data siswa berhasil ditambahkan.');
        $this->resetForm();
        $this->dispatch('refreshDatatable');
    }

    #[On('edit-siswa')]
    public function edit(int $id): void
    {
        $siswa = $this->baseSiswaQuery()->findOrFail($id);

        $this->siswaId   = $siswa->id;
        $this->kelasId   = $siswa->kelas_id;
        $this->nama      = $siswa->nama;
        $this->isEditing = true;
        $this->showForm  = true;
        $this->showImportForm = false;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    #[On('delete-siswa')]
    public function delete(int $id): void
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

        $siswa->delete();
        session()->flash('success', "Data siswa {$siswa->nama} berhasil dihapus.");
        $this->dispatch('siswa-deleted');
        $this->dispatch('refreshDatatable');
    }

    public function updatedFilterKelasNama(): void
    {
        $kelasNama = ($this->filterKelasNama === '0' || $this->filterKelasNama === '') ? '' : $this->filterKelasNama;
        $this->dispatch('siswa-filter-changed', kelasNama: $kelasNama, isGanjil: $this->filterIsGanjil);
    }

    public function updatedFilterIsGanjil(): void
    {
        $kelasNama = ($this->filterKelasNama === '0' || $this->filterKelasNama === '') ? '' : $this->filterKelasNama;
        $this->dispatch('siswa-filter-changed', kelasNama: $kelasNama, isGanjil: $this->filterIsGanjil);
    }

    public function updatedSearch(): void
    {
        $this->dispatch('siswa-search-changed', search: trim($this->search));
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->showImportForm = false;
        if ($this->showForm) {
            $this->reset(['siswaId', 'kelasId', 'nama', 'isEditing']);
            $this->resetValidation();
        }
    }

    public function toggleImportForm(): void
    {
        $this->showImportForm = ! $this->showImportForm;
        $this->showForm = false;
        if ($this->showImportForm) {
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
            'fileImport' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'fileImport.required' => 'Pilih file Excel yang akan diimport.',
        ]);

        $import = new SiswaImport($sekolah);
        Excel::import($import, $this->fileImport);

        $this->importFailures = $import->failures();

        if ($import->insertedCount() > 0) {
            session()->flash('success', $import->insertedCount() . ' data siswa berhasil diimport.');
        }

        if ($this->importFailures !== []) {
            session()->flash('warning', count($this->importFailures) . ' baris gagal diimport. Lihat keterangan di bawah form.');
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

        $kelasPart    = $this->filterKelasNama !== '' && $this->filterKelasNama !== '0'
            ? 'kelas ' . $this->filterKelasNama
            : 'semua kelas';

        $semesterPart = $this->filterIsGanjil === null
            ? 'semua semester'
            : ($this->filterIsGanjil === '1' ? 'ganjil' : 'genap');

        $filename = "data siswa {$kelasPart} {$semesterPart}.xlsx";

        return Excel::download(
            new SiswaExport($sekolah, $this->filterKelasNama !== '' ? $this->filterKelasNama : null, $this->filterIsGanjil),
            $filename,
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new SiswaTemplateExport(), 'template-import-siswa.xlsx');
    }

    private function resetForm(): void
    {
        $this->reset(['siswaId', 'kelasId', 'nama', 'isEditing']);
        $this->resetValidation();
        $this->showForm = false;
    }

    private function currentSekolah(): ?Sekolah
    {
        return Auth::user()?->sekolah;
    }

    private function baseSiswaQuery()
    {
        $sekolah = $this->currentSekolah();
        return Siswa::query()
            ->whereHas('kelas', fn ($q) => $q->where('sekolah_id', $sekolah?->id));
    }
}
