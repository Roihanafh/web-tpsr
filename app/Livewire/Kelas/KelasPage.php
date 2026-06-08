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

    public ?int $kelasId = null;

    public ?int $tahunAjarId = null;

    public ?int $filterTahunAjarId = null;

    public string $nama = '';

    public mixed $fileImport = null;

    public bool $isEditing = false;

    public array $importFailures = [];

    public function render(): View
    {
        return view('livewire.kelas.kelas-page', [
            'tahunAjarOptions' => TahunAjar::orderByDesc('id')->get(),
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
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function updatedFilterTahunAjarId(): void
    {
        $this->dispatch('kelas-filter-changed', tahunAjarId: $this->filterTahunAjarId);
    }

    public function import(): void
    {
        $sekolah = $this->currentSekolah();

        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');

            return;
        }

        $this->validate([
            'tahunAjarId' => ['required', 'exists:tahun_ajar,id'],
            'fileImport' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'tahunAjarId.required' => 'Pilih tahun ajaran sebelum import.',
            'fileImport.required' => 'Pilih file Excel yang akan diimport.',
        ]);

        $import = new KelasImport($sekolah, TahunAjar::findOrFail($this->tahunAjarId));
        Excel::import($import, $this->fileImport);

        $this->importFailures = $import->failures();

        if ($import->insertedCount() > 0) {
            session()->flash('success', $import->insertedCount().' data kelas berhasil diimport.');
        }

        if ($this->importFailures !== []) {
            session()->flash('warning', count($this->importFailures).' baris gagal diimport. Lihat keterangan di bawah form.');
        }

        $this->fileImport = null;
        $this->dispatch('refreshDatatable');
    }

    public function export(): BinaryFileResponse
    {
        $sekolah = $this->currentSekolah();

        if (! $sekolah) {
            session()->flash('error', 'Akun login saat ini belum terhubung dengan data sekolah.');
            $this->skipRender();
        }

        return Excel::download(
            new KelasExport($sekolah, $this->filterTahunAjarId),
            'data-kelas.xlsx',
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
