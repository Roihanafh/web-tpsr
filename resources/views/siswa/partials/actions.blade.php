<button
    type="button"
    class="btn btn-sm btn-outline-primary"
    wire:click="$dispatch('edit-siswa', { id: {{ $siswa->id }} })"
    wire:loading.attr="disabled"
>
    <i class="fas fa-edit mr-1"></i>
    Edit
</button>
