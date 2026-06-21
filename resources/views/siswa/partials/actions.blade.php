<div class="d-flex justify-content-center align-items-center gap-2">
    <button
        type="button"
        class="btn btn-sm btn-outline-primary"
        wire:click="$dispatch('edit-siswa', { id: {{ $siswa->id }} })"
        wire:loading.attr="disabled"
        style="width: 80px;"
    >
        <i class="fas fa-edit mr-1"></i>
        Edit
    </button>

    <button
        type="button"
        class="btn btn-sm btn-outline-danger"
        style="width: 80px;"
        onclick="Swal.fire({
            title: 'Hapus Data Siswa?',
            icon: 'warning',
            html: `
                <div class='text-left text-start'>
                    <div class='alert alert-warning mb-3' style='font-size: 0.9rem; text-align: left;'>
                        <i class='fas fa-exclamation-triangle mr-2 me-2'></i>
                        <strong>Peringatan:</strong> Menghapus data siswa ini juga akan menghapus seluruh data penilaian yang terkait!
                    </div>
                    <p style='text-align: left;'>Hapus siswa <strong>{{ addslashes($siswa->nama) }}</strong>
                    dari kelas <strong>{{ addslashes($siswa->kelas?->nama ?? '-') }}</strong>
                    ({{ $siswa->kelas?->is_ganjil ? 'Ganjil' : 'Genap' }})?</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('delete-siswa', { id: {{ $siswa->id }} });
            }
        })"
        wire:loading.attr="disabled"
    >
        <i class="fas fa-trash-alt mr-1"></i>
        Hapus
    </button>
</div>
