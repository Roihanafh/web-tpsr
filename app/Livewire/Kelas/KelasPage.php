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

    public ?int  $kelasId       = null;
    public string $nama         = '';
    public string $search       = '';
    public mixed  $fileImport   = null;
    public bool   $isEditing    = false;
    public bool   $showForm     = false;
    public bool   $showImportForm = false;
    public array  $importFailures = [];

    public function render(): View
    {
        return view('livewire.kelas.kelas-page', [
            'sekolah'        => $this->currentSekolah(),
            'showForm'       => $this->showForm,
            'showImportForm' => $this->showImportForm,
            'importFailures' => $this->importFailures,
            'search'         => $this->search,
        ]);
    }

    public function save(): void
    {
        $sekolah = $this->currentSekolah();
        if (! $sekolah) {
            session()->flash('error', 'Akun belum terhubung dengan sekolah.');
            return;
        }

        $validated = $this->validate([
            'nama' => ['required', 'string', 'max:20',
                Rule::unique('kelas', 'nama')
                    ->where('sekolah_id', $sekolah->id)
                    ->when($this->isEditing, fn ($r) => $r->ignore($this->kelasId)),
            ],
        ], [
            'nama.unique' => 'Kelas dengan nama tersebut sudah ada di sekolah ini.',
        ]);

        if ($this->isEditing) {
            Kelas::where('id', $this->kelasId)->update(['nama' => strtoupper($validated['nama'])]);
            session()->flash('success', 'Data kelas berhasil diperbarui.');
        } else {
            Kelas::create(['sekolah_id' => $sekolah->id, 'nama' => strtoupper($validated['nama'])]);
            session()->flash('success', 'Data kelas berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->dispatch('refreshDatatable');
    }

    #[On('edit-kelas')]
    public function edit(int $id): void
    {
        $kelas = $this->baseKelasQuery()->findOrFail($id);
        $this->kelasId   = $kelas->id;
        $this->nama      = $kelas->nama;
        $this->isEditing = true;
        $this->showForm  = true;
        $this->showImportForm = false;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    #[On('delete-kelas')]
    public function delete(int $id): void
    {
        $kelas = $this->baseKelasQuery()->find($id);
        if (! $kelas) { session()->flash('error', 'Data kelas tidak ditemukan.'); return; }
        $nama = $kelas->nama;
        $kelas->delete();
        session()->flash('success', "Kelas {$nama} berhasil dihapus.");
        $this->dispatch('refreshDatatable');
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
        }
    }

    public function toggleImportForm(): void
    {
        $this->showImportForm = ! $this->showImportForm;
        $this->showForm = false;
        if ($this->showImportForm) $this->fileImport = null;
    }

    public function import(): void
    {
        $sekolah = $this->currentSekolah();
        if (! $sekolah) { session()->flash('error', 'Akun belum terhubung dengan sekolah.'); return; }

        $this->validate(['fileImport' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $import = new KelasImport($sekolah);
        Excel::import($import, $this->fileImport);
        $this->importFailures = $import->failures();

        if ($import->insertedCount() > 0) session()->flash('success', $import->insertedCount() . ' data kelas berhasil diimport.');
        if ($this->importFailures !== []) session()->flash('warning', count($this->importFailures) . ' baris gagal diimport.');

        $this->fileImport = null;
        $this->showImportForm = false;
        $this->dispatch('refreshDatatable');
    }

    public function export(): BinaryFileResponse
    {
        $sekolah = $this->currentSekolah();
        if (! $sekolah) { session()->flash('error', 'Akun belum terhubung dengan sekolah.'); $this->skipRender(); }
        return Excel::download(new KelasExport($sekolah), 'data-kelas.xlsx');
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
        return Kelas::query()->where('sekolah_id', $this->currentSekolah()?->id);
    }
}
