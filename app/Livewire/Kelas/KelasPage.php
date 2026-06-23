<?php

namespace App\Livewire\Kelas;

use App\Models\Kelas;
use App\Models\Sekolah;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class KelasPage extends Component
{
    public ?int  $kelasId       = null;
    public string $nama         = '';
    public string $search       = '';
    public bool   $isEditing    = false;
    public bool   $showForm     = false;

    public function render(): View
    {
        return view('livewire.kelas.kelas-page', [
            'sekolah'        => $this->currentSekolah(),
            'showForm'       => $this->showForm,
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

    #[On('delete-all-kelas')]
    public function deleteAll(): void
    {
        $sekolah = $this->currentSekolah();
        if (! $sekolah) {
            session()->flash('error', 'Akun belum terhubung dengan sekolah.');
            return;
        }

        $count = $sekolah->kelas()->count();
        if ($count === 0) {
            session()->flash('warning', 'Tidak ada data kelas untuk dihapus.');
            return;
        }

        $sekolah->kelas()->delete();

        session()->flash('success', 'Seluruh data kelas dan data terkait berhasil dihapus.');
        $this->dispatch('refreshDatatable');
    }

    public function updatedSearch(): void
    {
        $this->dispatch('kelas-search-changed', search: trim($this->search));
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        if ($this->showForm) {
            $this->reset(['kelasId', 'nama', 'isEditing']);
            $this->resetValidation();
        }
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

    public function mount(): void
    {
        $this->dispatch('refreshDatatable');
    }
}
