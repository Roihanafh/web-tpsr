<?php

namespace App\Livewire\Admin;

use App\Models\TahunAjar;
use App\Models\Kelas;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TahunAjarPage extends Component
{
    public string $tahun = ''; // Digunakan untuk Add mode (format 2025/2026)

    public string $nama = ''; // Digunakan untuk Edit mode (misal 2025/2026 Ganjil)

    public ?int $tahunAjarId = null;

    public bool $isEditing = false;

    public bool $showForm = false;

    public string $search = '';

    public bool $loadData = false;

    public function render(): View
    {
        return view('livewire.admin.tahun-ajar-page', [
            'showForm' => $this->showForm,
            'isEditing' => $this->isEditing,
        ]);
    }

    public function tampilkanData(): void
    {
        $this->loadData = true;
        $this->dispatch('refreshDatatable');
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if ($this->showForm) {
            $this->reset(['tahun', 'nama', 'tahunAjarId', 'isEditing']);
            $this->resetValidation();
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        if ($this->isEditing) {
            $validated = $this->validate([
                'nama' => ['required', 'string', 'max:255', 'unique:tahun_ajar,nama,' . $this->tahunAjarId],
            ], [
                'nama.required' => 'Nama tahun ajaran wajib diisi.',
                'nama.unique' => 'Nama tahun ajaran tersebut sudah ada.',
            ]);

            $tahunAjar = TahunAjar::findOrFail($this->tahunAjarId);
            $tahunAjar->update([
                'nama' => trim($validated['nama']),
            ]);

            session()->flash('success', 'Tahun ajaran berhasil diperbarui.');
        } else {
            $validated = $this->validate([
                'tahun' => ['required', 'string', 'regex:/^[0-9]{4}\/[0-9]{4}$/'],
            ], [
                'tahun.required' => 'Tahun ajaran wajib diisi.',
                'tahun.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2025/2026).',
            ]);

            $tahunInput = trim($validated['tahun']);
            $ganjilName = $tahunInput . ' Ganjil';
            $genapName = $tahunInput . ' Genap';

            // Check if either exists
            $existsGanjil = TahunAjar::where('nama', $ganjilName)->exists();
            $existsGenap = TahunAjar::where('nama', $genapName)->exists();

            if ($existsGanjil || $existsGenap) {
                $this->addError('tahun', 'Tahun ajaran ' . $tahunInput . ' sudah terdaftar di database.');
                return;
            }

            // Create both
            TahunAjar::create(['nama' => $ganjilName]);
            TahunAjar::create(['nama' => $genapName]);

            session()->flash('success', 'Tahun ajaran ' . $tahunInput . ' (Ganjil & Genap) berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->loadData = true;
        $this->dispatch('refreshDatatable');
    }

    #[On('edit-tahun-ajar')]
    public function edit(int $id): void
    {
        $tahunAjar = TahunAjar::findOrFail($id);
        $this->tahunAjarId = $tahunAjar->id;
        $this->nama = $tahunAjar->nama;
        $this->isEditing = true;
        $this->showForm = true;
    }

    #[On('delete-tahun-ajar')]
    public function delete(int $id): void
    {
        $tahunAjar = TahunAjar::findOrFail($id);

        // Check if there are related classes
        $hasKelas = Kelas::where('tahun_ajar_id', $id)->exists();

        if ($hasKelas) {
            session()->flash('error', 'Tahun ajaran "' . $tahunAjar->nama . '" tidak dapat dihapus karena masih terhubung dengan data kelas.');
            return;
        }

        $tahunAjar->delete();
        session()->flash('success', 'Tahun ajaran "' . $tahunAjar->nama . '" berhasil dihapus.');
        $this->dispatch('refreshDatatable');
    }

    public function updatedSearch(): void
    {
        $this->dispatch('tahun-ajar-search-changed', search: trim($this->search));
    }

    private function resetForm(): void
    {
        $this->reset(['tahun', 'nama', 'tahunAjarId', 'isEditing', 'showForm']);
        $this->resetValidation();
    }
}
