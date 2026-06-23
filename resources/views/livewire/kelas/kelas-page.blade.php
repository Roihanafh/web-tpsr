<div class="kelas-page">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @unless ($sekolah)
        <div class="alert alert-info">
            Akun ini belum memiliki relasi sekolah. Data kelas hanya bisa dikelola oleh user yang terhubung dengan sekolah.
        </div>
    @endunless

    <div class="kelas-panel">
        <div class="kelas-toolbar">
            <div class="kelas-actions">
                <div class="kelas-search-wrap">
                    <span class="kelas-search-icon"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control kelas-search" placeholder="Search"
                        wire:model.live.debounce.300ms="search">
                </div>

                <button type="button" class="btn btn-primary kelas-btn" wire:click="toggleForm" wire:loading.attr="disabled">
                    <i class="fas fa-plus mr-1"></i>Tambah Kelas
                </button>

                @if ($sekolah)
                    <button type="button" class="btn btn-danger kelas-btn"
                        onclick="confirmDeleteAllKelas()"
                        wire:loading.attr="disabled">
                        <i class="fas fa-trash-alt mr-1"></i>Hapus Semua Kelas
                    </button>
                @endif
            </div>
        </div>

        @if ($showForm)
            <div class="kelas-form-grid position-relative mt-3">
                <div class="kelas-loading-layer" wire:loading.flex wire:target="save,cancelEdit">
                    <div class="kelas-loading-box">
                        <i class="fas fa-spinner fa-spin"></i><span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="save" class="kelas-form">
                    <div class="form-row">
                        <div class="col-md-6 mb-2">
                            <label>Nama Kelas</label>
                            <input type="text" maxlength="20"
                                class="form-control @error('nama') is-invalid @enderror"
                                wire:model="nama" placeholder="Contoh 5-A"
                                wire:loading.attr="disabled" wire:target="save">
                            @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6 mb-2 kelas-submit d-flex align-items-end pb-1">
                            <button type="submit" class="btn btn-primary kelas-btn mr-2"
                                wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fas fa-{{ $isEditing ? 'save' : 'plus' }} mr-1"></i>
                                    {{ $isEditing ? 'Simpan Edit' : 'Tambah Kelas' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan
                                </span>
                            </button>

                            @if ($isEditing)
                                <button type="button" class="btn btn-outline-secondary kelas-btn"
                                    wire:click="cancelEdit">Batal</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @endif



        <div class="kelas-table-shell">
            <div class="kelas-table-head">
                <div>
                    <div class="kelas-table-title">Tabel Data Kelas</div>
                    <div class="kelas-table-subtitle">Menampilkan seluruh data kelas di sekolah ini.</div>
                </div>
            </div>

            <div class="kelas-table-wrap">
                <div class="kelas-table-loading" wire:loading.flex wire:target="search">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Memuat data kelas...
                </div>
                <livewire:kelas.kelas-table :key="'kelas-table'" />
            </div>
        </div>
    </div>
</div>
