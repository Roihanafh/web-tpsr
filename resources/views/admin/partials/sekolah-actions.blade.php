<button
    type="button"
    class="btn btn-sm btn-outline-primary"
    wire:click="$dispatch('edit-sekolah', { id: {{ $sekolah->id }} })"
    wire:loading.attr="disabled"
>
    <i class="fas fa-edit mr-1"></i>
    Edit
</button>

<button
    type="button"
    class="btn btn-sm btn-outline-info"
    wire:click="$dispatch('view-sekolah-teachers', { id: {{ $sekolah->id }} })"
    wire:loading.attr="disabled"
>
    <i class="fas fa-users mr-1"></i>
    Lihat Guru
</button>

<button
    type="button"
    class="btn btn-sm btn-outline-danger"
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
    <i class="fas fa-trash-alt mr-1"></i>
    Hapus
</button>
