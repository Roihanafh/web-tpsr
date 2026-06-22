<div class="d-flex justify-content-center align-items-center" style="gap: 0.35rem; flex-wrap: nowrap;">
    <button
        type="button"
        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center"
        style="gap: 4px;"
        wire:click="$dispatch('edit-sekolah', { id: {{ $sekolah->id }} })"
        wire:loading.attr="disabled"
    >
        <i class="fas fa-edit"></i>
        <span>Edit</span>
    </button>

    <button
        type="button"
        class="btn btn-sm btn-outline-info d-inline-flex align-items-center"
        style="gap: 4px;"
        wire:click="$dispatch('view-sekolah-teachers', { id: {{ $sekolah->id }} })"
        wire:loading.attr="disabled"
    >
        <i class="fas fa-users"></i>
        <span>Guru</span>
    </button>

    <button
        type="button"
        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center"
        style="gap: 4px;"
        onclick="Swal.fire({
            title: 'Hapus Sekolah?',
            text: 'Apakah Anda yakin ingin menghapus sekolah \'{{ addslashes($sekolah->nama) }}\'? Tindakan ini tidak dapat dibatalkan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('delete-sekolah', { id: {{ $sekolah->id }} });
            }
        })"
        wire:loading.attr="disabled"
    >
        <i class="fas fa-trash-alt"></i>
        <span>Hapus</span>
    </button>
</div>
