<div class="d-flex justify-content-center align-items-center gap-2">
    <button
        type="button"
        class="btn btn-sm btn-outline-primary"
        wire:click="$dispatch('edit-kelas', { id: {{ $kelas->id }} })"
        wire:loading.attr="disabled"
        style="width: 80px;"
    >
        <i class="fas fa-edit mr-1"></i>
        Edit
    </button>

    @php
        $year = trim(str_ireplace(['ganjil', 'genap'], '', $kelas->tahunAjar->nama ?? ''));
        $semester1 = $year . ' Ganjil';
        $semester2 = $year . ' Genap';
    @endphp

    <button
        type="button"
        class="btn btn-sm btn-outline-danger"
        style="width: 80px;"
        onclick="Swal.fire({
            title: 'Hapus Data Kelas?',
            icon: 'warning',
            html: `
                <div class='text-left text-start'>
                    <div class='alert alert-warning mb-3' style='font-size: 0.9rem; text-align: left;'>
                        <i class='fas fa-exclamation-triangle mr-2 me-2'></i>
                        <strong>Peringatan:</strong> Menghapus kelas ini juga akan menghapus seluruh data siswa yang terdaftar di kelas ini beserta seluruh data penilaian mereka pada kedua semester!
                    </div>
                    <p class='mb-2' style='text-align: left;'>Apakah Anda yakin ingin menghapus kelas <strong>{{ addslashes($kelas->nama) }}</strong> pada tahun ajaran ini? Tindakan ini akan menghapus record kelas untuk kedua semester:</p>
                    <ul class='mb-0' style='text-align: left; padding-left: 20px;'>
                        <li><strong>{{ addslashes($kelas->nama) }} ({{ addslashes($semester1) }})</strong></li>
                        <li><strong>{{ addslashes($kelas->nama) }} ({{ addslashes($semester2) }})</strong></li>
                    </ul>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('delete-kelas', { id: {{ $kelas->id }} });
            }
        })"
        wire:loading.attr="disabled"
    >
        <i class="fas fa-trash-alt mr-1"></i>
        Hapus
    </button>
</div>

