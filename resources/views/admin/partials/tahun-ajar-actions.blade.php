<button
    type="button"
    class="btn btn-sm btn-outline-primary"
    wire:click="$dispatch('edit-tahun-ajar', { id: {{ $tahunAjar->id }} })"
    wire:loading.attr="disabled"
>
    <i class="fas fa-edit mr-1"></i>
    Edit
</button>

<button
    type="button"
    class="btn btn-sm btn-outline-danger"
    onclick="Swal.fire({
        title: 'Hapus Tahun Ajaran?',
        text: 'Apakah Anda yakin ingin menghapus tahun ajaran \'{{ addslashes($tahunAjar->nama) }}\'? Tindakan ini tidak dapat dibatalkan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Livewire.dispatch('delete-tahun-ajar', { id: {{ $tahunAjar->id }} });
        }
    })"
    wire:loading.attr="disabled"
>
    <i class="fas fa-trash-alt mr-1"></i>
    Hapus
</button>
