<?php

namespace App\Livewire\Kelas;

use App\Exports\KelasExport;
use App\Exports\KelasTemplateExport;
use App\Imports\KelasImport;
use App\Models\Kelas;
use App\Models\Sekolah;
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

    public ?int $kelasId       = null;
    public string $nama        = '';
    public ?bool $isGanjil     = null;   // filter tabel
    public ?bool $formIsGanjil = null;   // form add/edit
    public string $search      = '';
    public mixed $fileImport   = null;
    public bool $isEditing     = false;
    public bool $showForm      = false;
    public bool $showImportForm = false;
    public array $importFailures = [];

    public function render(): View
    {
        return view('livewire.kelas.kelas-page', [
            'sekolah'          => $this->currentSekolah(),
            'showForm'         => $this->showForm,
            'showImportForm'   => $this->showImportForm,
            'importFailures'   => $this->importFailures,
            'filterIsGanjil'   => $this->isGanjil,
            'search'           => $this->search,
        ]);
    }

    public function save(): void
    {
        $sekolah = $this->currentSekolah();

        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');
            return;
        }

        $validated = $this->validate([
            'nama'        => ['required', 'string', 'max:20',
                Rule::unique('kelas', 'nama')
                    ->where('sekolah_id', $sekolah->id)
                    ->where('is_ganjil', $this->formIsGanjil)
                    ->when($this->isEditing, fn ($r) => $r->ignore($this->kelasId)),
            ],
            'formIsGanjil' => ['required', 'boolean'],
        ], [
            'nama.unique'          => 'Kelas dengan nama dan semester tersebut sudah ada.',
            'formIsGanjil.required' => 'Semester wajib dipilih.',
        ]);

        if ($this->isEditing) {
            Kelas::where('id', $this->kelasId)->update([
                'nama'      => strtoupper($validated['nama']),
                'is_ganjil' => $validated['formIsGanjil'],
            ]);
            session()->flash('success', 'Data kelas berhasil diperbarui.');
        } else {
            Kelas::create([
                'sekolah_id' => $sekolah->id,
                'nama'       => strtoupper($validated['nama']),
                'is_ganjil'  => $validated['formIsGanjil'],
            ]);
            session()->flash('success', 'Data kelas berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->dispatch('refreshDatatable');
    }

    #[On('edit-kelas')]
    public function edit(int $id): void
    {
        $kelas = $this->baseKelasQuery()->findOrFail($id);

        $this->kelasId      = $kelas->id;
        $this->nama         = $kelas->nama;
        $this->formIsGanjil = (bool) $kelas->is_ganjil;
        $this->isEditing    = true;
        $this->showForm     = true;
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

        $kelas = $this->baseKelasQuery()->find($id);
        if (! $kelas) {
            session()->flash('error', 'Data kelas tidak ditemukan.');
            return;
        }

        $namaKelas = $kelas->nama;
        $kelas->delete();

        session()->flash('success', "Kelas {$namaKelas} berhasil dihapus.");
        $this->dispatch('refreshDatatable');
    }

    public function updatedIsGanjil(): void
    {
        $this->dispatch('kelas-filter-changed', isGanjil: $this->isGanjil);
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
            $this->reset(['kelasId', 'nama', 'formIsGanjil', 'isEditing']);
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

        $import = new KelasImport($sekolah);
        Excel::import($import, $this->fileImport);

        $this->importFailures = $import->failures();

        if ($import->insertedCount() > 0) {
            session()->flash('success', $import->insertedCount() . ' data kelas berhasil diimport.');
        }

        if ($this->importFailures !== []) {
            session()->flash('warning', count($this->importFailures) . ' baris gagal diimport.');
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

        $semesterPart = $this->isGanjil === null
            ? 'semua semester'
            : ($this->isGanjil ? 'ganjil' : 'genap');

        $filename = "data kelas {$semesterPart}.xlsx";

        return Excel::download(
            new KelasExport($sekolah, $this->isGanjil),
            $filename,
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new KelasTemplateExport(), 'template-import-kelas.xlsx');
    }

    private function resetForm(): void
    {
        $this->reset(['kelasId', 'nama', 'formIsGanjil', 'isEditing']);
        $this->resetValidation();
        $this->showForm = false;
    }

    private function currentSekolah(): ?Sekolah
    {
        return Auth::user()?->sekolah;
    }

    private function baseKelasQuery()
    {
        return Kelas::query()->where('sekolah_id', $this->currentSekolah()?->id);
    }
}
