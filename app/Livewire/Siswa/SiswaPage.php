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

    public ?int $siswaId = null;

    public ?int $kelasId = null;

    public ?int $tahunAjarId = null;

    public string $filterKelasNama = '';

    public ?int $filterTahunAjarId = null;

    public string $search = '';

    public string $nama = '';

    public string $gender = '';

    public mixed $fileImport = null;

    public bool $isEditing = false;

    public bool $showForm = false;

    public bool $showImportForm = false;

    public array $importFailures = [];

    public function render(): View
    {
        $sekolah = $this->currentSekolah();

        return view('livewire.siswa.siswa-page', [
            'kelasOptions' => $sekolah
                ? $sekolah->kelas()
                    ->when($this->tahunAjarId, fn ($query) => $query->where('tahun_ajar_id', $this->tahunAjarId))
                    ->orderBy('nama')
                    ->get()
                : collect(),
            'filterKelasOptions' => $sekolah
                ? $sekolah->kelas()
                    ->select('nama')
                    ->distinct()
                    ->orderBy('nama')
                    ->pluck('nama')
                : collect(),
            'tahunAjarOptions' => TahunAjar::orderByDesc('id')->get(),
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

    public function updatedFilterKelasNama(): void
    {
        $this->dispatch('siswa-filter-changed', kelasNama: $this->filterKelasNama, tahunAjarId: $this->filterTahunAjarId);
    }

    public function updatedFilterTahunAjarId(): void
    {
        $this->dispatch('siswa-filter-changed', kelasNama: $this->filterKelasNama, tahunAjarId: $this->filterTahunAjarId);
    }

    public function updatedSearch(): void
    {
        $this->dispatch('siswa-search-changed', search: trim($this->search));
    }

    public function updatedTahunAjarId(): void
    {
        $this->kelasId = null;
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->showImportForm = false;
    }

    public function toggleImportForm(): void
    {
        $this->showImportForm = ! $this->showImportForm;
        $this->showForm = false;
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

        return Excel::download(
            new SiswaExport($sekolah, $this->filterKelasNama !== '' ? $this->filterKelasNama : null, $this->filterTahunAjarId ?: null),
            'data-siswa.xlsx',
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new SiswaTemplateExport(), 'template-import-siswa.xlsx');
    }

    private function resetForm(): void
    {
        $this->reset(['siswaId', 'kelasId', 'tahunAjarId', 'nama', 'gender', 'isEditing']);
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
