<?php

namespace App\Livewire\Admin;

use App\Models\Sekolah;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class SekolahPage extends Component
{
    public string $nama = '';

    public string $alamat = '';

    public ?int $sekolahId = null;

    public bool $isEditing = false;

    public bool $showForm = false;

    public string $search = '';

    public bool $loadData = true;

    public function mount(): void
    {
        $this->dispatch('refreshDatatable');
    }

    // Properties for teacher modal
    public ?int $selectedSekolahId = null;

    public string $selectedSekolahNama = '';

    public array $teachersList = [];

    public function render(): View
    {
        return view('livewire.admin.sekolah-page', [
            'showForm' => $this->showForm,
            'isEditing' => $this->isEditing,
        ]);
    }



    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if ($this->showForm) {
            $this->reset(['nama', 'alamat', 'sekolahId', 'isEditing']);
            $this->resetValidation();
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:sekolah,nama,' . $this->sekolahId],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama sekolah wajib diisi.',
            'nama.unique' => 'Nama sekolah tersebut sudah ada.',
        ]);

        Sekolah::updateOrCreate(
            ['id' => $this->sekolahId],
            [
                'nama' => trim($validated['nama']),
                'alamat' => isset($validated['alamat']) ? trim($validated['alamat']) : null,
            ]
        );

        session()->flash('success', $this->isEditing ? 'Data sekolah berhasil diperbarui.' : 'Data sekolah berhasil ditambahkan.');

        $this->resetForm();
        $this->loadData = true;
        $this->dispatch('refreshDatatable');
    }

    #[On('edit-sekolah')]
    public function edit(int $id): void
    {
        $sekolah = Sekolah::findOrFail($id);
        $this->sekolahId = $sekolah->id;
        $this->nama = $sekolah->nama;
        $this->alamat = $sekolah->alamat ?? '';
        $this->isEditing = true;
        $this->showForm = true;
    }

    #[On('delete-sekolah')]
    public function delete(int $id): void
    {
        $sekolah = Sekolah::findOrFail($id);

        // Check if there are related users
        $hasUsers = User::where('sekolah_id', $id)->exists();
        if ($hasUsers) {
            session()->flash('error', 'Sekolah "' . $sekolah->nama . '" tidak dapat dihapus karena masih memiliki guru/user yang terhubung.');
            return;
        }

        // Check if there are related classes
        $hasKelas = Kelas::where('sekolah_id', $id)->exists();
        if ($hasKelas) {
            session()->flash('error', 'Sekolah "' . $sekolah->nama . '" tidak dapat dihapus karena masih terhubung dengan data kelas.');
            return;
        }

        $sekolah->delete();
        session()->flash('success', 'Sekolah "' . $sekolah->nama . '" berhasil dihapus.');
        $this->dispatch('refreshDatatable');
    }

    #[On('view-sekolah-teachers')]
    public function showTeachers(int $id): void
    {
        $sekolah = Sekolah::findOrFail($id);
        $this->selectedSekolahId = $sekolah->id;
        $this->selectedSekolahNama = $sekolah->nama;

        $this->teachersList = User::where('sekolah_id', $id)
            ->get()
            ->map(fn (User $user) => [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->implode(', '),
            ])
            ->toArray();

        $this->dispatch('open-teachers-modal');
    }

    public function updatedSearch(): void
    {
        $this->dispatch('sekolah-search-changed', search: trim($this->search));
    }

    private function resetForm(): void
    {
        $this->reset(['nama', 'alamat', 'sekolahId', 'isEditing', 'showForm']);
        $this->resetValidation();
    }
}
