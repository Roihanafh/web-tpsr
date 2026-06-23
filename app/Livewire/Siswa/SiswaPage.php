<?php

namespace App\Livewire\Siswa;

use App\Models\Sekolah;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class SiswaPage extends Component
{
    public ?int   $siswaId  = null;
    public ?int   $kelasId  = null;
    public string $nama     = '';

    public string $filterKelasNama = '';
    public string $search          = '';

    public bool $isEditing      = false;
    public bool $showForm       = false;

    public function mount(): void
    {
        $this->dispatch('refreshDatatable');
    }

    public function render(): View
    {
        $sekolah = $this->currentSekolah();

        $filterKelasOptions = $sekolah
            ? $sekolah->kelas()->select('nama')->distinct()->orderBy('nama')->pluck('nama')
            : collect();

        $kelasOptions = $sekolah
            ? $sekolah->kelas()->orderBy('nama')->get()
            : collect();

        return view('livewire.siswa.siswa-page', [
            'sekolah'            => $sekolah,
            'filterKelasOptions' => $filterKelasOptions,
            'kelasOptions'       => $kelasOptions,
            'filterKelasNama'    => $this->filterKelasNama,
            'search'             => $this->search,
            'showForm'           => $this->showForm,
        ]);
    }

    public function save(): void
    {
        $sekolah = $this->currentSekolah();
        if (! $sekolah) { session()->flash('error', 'Akun belum terhubung dengan sekolah.'); return; }

        if ($this->isEditing) {
            $validated = $this->validate([
                'nama'    => ['required', 'string', 'max:100',
                    Rule::unique('siswa', 'nama')->where('kelas_id', $this->kelasId)->ignore($this->siswaId),
                ],
                'kelasId' => ['required', 'exists:kelas,id'],
            ]);
            Siswa::where('id', $this->siswaId)->update(['kelas_id' => $validated['kelasId'], 'nama' => $validated['nama']]);
        } else {
            $validated = $this->validate([
                'nama'    => ['required', 'string', 'max:100'],
                'kelasId' => ['required', 'exists:kelas,id'],
            ]);

            if (! $sekolah->kelas()->whereKey($validated['kelasId'])->exists()) {
                $this->addError('kelasId', 'Kelas tidak ditemukan di sekolah ini.');
                return;
            }

            if (Siswa::where('kelas_id', $validated['kelasId'])->where('nama', $validated['nama'])->exists()) {
                $this->addError('nama', 'Nama siswa dengan kelas tersebut sudah ada.');
                return;
            }

            Siswa::create(['kelas_id' => $validated['kelasId'], 'nama' => $validated['nama'], 'rata_poin' => 0]);
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
    }

    public function cancelEdit(): void { $this->resetForm(); }

    #[On('delete-siswa')]
    public function delete(int $id): void
    {
        $siswa = $this->baseSiswaQuery()->find($id);
        if (! $siswa) { session()->flash('error', 'Data siswa tidak ditemukan.'); return; }
        $siswa->delete();
        session()->flash('success', "Data siswa {$siswa->nama} berhasil dihapus.");
        $this->dispatch('siswa-deleted');
        $this->dispatch('refreshDatatable');
    }

    #[On('delete-all-siswa')]
    public function deleteAll(?string $kelasNama = null): void
    {
        $sekolah = $this->currentSekolah();
        if (! $sekolah) {
            session()->flash('error', 'Akun belum terhubung dengan sekolah.');
            return;
        }

        if ($kelasNama && $kelasNama !== '0' && $kelasNama !== '') {
            $kelas = $sekolah->kelas()->where('nama', $kelasNama)->first();
            if (! $kelas) {
                session()->flash('error', "Kelas {$kelasNama} tidak ditemukan.");
                return;
            }

            $count = Siswa::where('kelas_id', $kelas->id)->count();
            if ($count === 0) {
                session()->flash('warning', "Tidak ada data siswa di kelas {$kelasNama} untuk dihapus.");
                return;
            }

            Siswa::where('kelas_id', $kelas->id)->delete();
            session()->flash('success', "Seluruh data siswa di kelas {$kelasNama} dan data terkait berhasil dihapus.");
        } else {
            $kelasIds = $sekolah->kelas()->pluck('id');
            $count = Siswa::whereIn('kelas_id', $kelasIds)->count();
            if ($count === 0) {
                session()->flash('warning', 'Tidak ada data siswa untuk dihapus.');
                return;
            }

            Siswa::whereIn('kelas_id', $kelasIds)->delete();
            session()->flash('success', 'Seluruh data siswa dan data terkait di sekolah ini berhasil dihapus.');
        }

        $this->dispatch('refreshDatatable');
    }

    public function updatedFilterKelasNama(): void
    {
        $kelasNama = ($this->filterKelasNama === '0' || $this->filterKelasNama === '') ? '' : $this->filterKelasNama;
        $this->dispatch('siswa-filter-changed', kelasNama: $kelasNama);
    }

    public function updatedSearch(): void
    {
        $this->dispatch('siswa-search-changed', search: trim($this->search));
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        if ($this->showForm) { $this->reset(['siswaId', 'kelasId', 'nama', 'isEditing']); $this->resetValidation(); }
    }



    private function resetForm(): void
    {
        $this->reset(['siswaId', 'kelasId', 'nama', 'isEditing']);
        $this->resetValidation();
        $this->showForm = false;
    }

    private function currentSekolah(): ?Sekolah { return Auth::user()?->sekolah; }

    private function baseSiswaQuery()
    {
        return Siswa::query()->whereHas('kelas', fn ($q) => $q->where('sekolah_id', $this->currentSekolah()?->id));
    }
}
