<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Sekolah;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

class UserPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'guru';

    public ?int $sekolahId = null;

    public ?int $userId = null;

    public bool $isEditing = false;

    public bool $showForm = false;

    public string $search = '';

    public bool $loadData = true;

    public function mount(): void
    {
        $this->dispatch('refreshDatatable');
    }

    public function render(): View
    {
        $sekolahOptions = Sekolah::orderBy('nama')->get();

        return view('livewire.admin.user-page', [
            'sekolahOptions' => $sekolahOptions,
            'showForm' => $this->showForm,
            'isEditing' => $this->isEditing,
        ]);
    }



    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if ($this->showForm) {
            $this->reset(['name', 'email', 'password', 'role', 'sekolahId', 'userId', 'isEditing']);
            $this->resetValidation();
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->userId],
            'password' => $this->isEditing ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,guru'],
            'sekolahId' => ['nullable', 'exists:sekolah,id'],
        ];

        $validated = $this->validate($rules, [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        $userData = [
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'sekolah_id' => $validated['sekolahId'] ?: null,
        ];

        if ($this->password !== '') {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $userData);
        $user->syncRoles([$validated['role']]);

        session()->flash('success', $this->isEditing ? 'Data user berhasil diperbarui.' : 'Data user berhasil ditambahkan.');

        $this->resetForm();
        $this->loadData = true;
        $this->dispatch('refreshDatatable');
    }

    #[On('edit-user')]
    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = ''; // Kosongkan password saat edit
        $this->role = $user->roles->pluck('name')->first() ?: 'guru';
        $this->sekolahId = $user->sekolah_id;
        $this->isEditing = true;
        $this->showForm = true;
    }

    #[On('delete-user')]
    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        $user = User::findOrFail($id);
        $user->delete();

        session()->flash('success', 'User "' . $user->name . '" berhasil dihapus.');
        $this->dispatch('refreshDatatable');
    }

    public function updatedSearch(): void
    {
        $this->dispatch('user-search-changed', search: trim($this->search));
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'email', 'password', 'role', 'sekolahId', 'userId', 'isEditing', 'showForm']);
        $this->resetValidation();
    }
}
