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
                    <p class='mb-2' style='text-align: left;'>Pilih opsi penghapusan untuk siswa <strong>{{ addslashes($siswa->nama) }}</strong>:</p>
                    <div class='form-check mb-2' style='text-align: left;'>
                        <input class='form-check-input' type='radio' name='deleteOption' id='deleteSemester_{{ $siswa->id }}' value='semester' checked>
                        <label class='form-check-label' for='deleteSemester_{{ $siswa->id }}'>
                            Hapus hanya untuk 1 semester ini saja ({{ addslashes($siswa->kelas?->tahunAjar?->nama ?? '') }})
                        </label>
                    </div>
                    <div class='form-check' style='text-align: left;'>
                        <input class='form-check-input' type='radio' name='deleteOption' id='deleteAll_{{ $siswa->id }}' value='all'>
                        <label class='form-check-label' for='deleteAll_{{ $siswa->id }}'>
                            Hapus semua data siswa ini di semua semester & tahun ajaran
                        </label>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const el = document.querySelector('input[name=deleteOption]:checked');
                return { option: el ? el.value : 'semester' };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('delete-siswa', { id: {{ $siswa->id }}, option: result.value.option });
            }
        })"
        wire:loading.attr="disabled"
    >
        <i class="fas fa-trash-alt mr-1"></i>
        Hapus
    </button>
</div>
