<div class="siswa-page">
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
            Akun ini belum memiliki relasi sekolah. Data siswa hanya bisa dikelola oleh user yang terhubung dengan sekolah.
        </div>
    @endunless

    <div class="siswa-panel">
        <div class="siswa-toolbar">
            <div class="siswa-toolbar-filters">
                <select class="form-control siswa-select" wire:model.live="filterKelasNama">
                    <option value="">Semua Kelas</option>
                    @foreach ($filterKelasOptions as $kNama)
                        <option value="{{ $kNama }}">{{ $kNama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="siswa-actions">
                <div class="siswa-search-wrap">
                    <span class="siswa-search-icon"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control siswa-search" placeholder="Search"
                        wire:model.live.debounce.300ms="search">
                </div>

                <button type="button" class="btn btn-primary siswa-btn" wire:click="toggleForm" wire:loading.attr="disabled">
                    <i class="fas fa-plus mr-1"></i>Tambah Siswa
                </button>

                @if ($sekolah)
                    <button type="button" class="btn btn-danger siswa-btn"
                        onclick="confirmDeleteAllSiswa('{{ addslashes($filterKelasNama) }}')"
                        wire:loading.attr="disabled">
                        <i class="fas fa-trash-alt mr-1"></i>
                        {{ $filterKelasNama && $filterKelasNama !== '0' && $filterKelasNama !== '' ? 'Hapus Siswa Kelas ' . $filterKelasNama : 'Hapus Semua Siswa' }}
                    </button>
                @endif
            </div>
        </div>

        @if ($showForm)
            <div class="siswa-form-grid position-relative mt-3">
                <div class="siswa-loading-layer" wire:loading.flex wire:target="save,cancelEdit">
                    <div class="siswa-loading-box">
                        <i class="fas fa-spinner fa-spin"></i><span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="save" class="siswa-form">
                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <label>Nama Siswa</label>
                            <input type="text" maxlength="100"
                                class="form-control @error('nama') is-invalid @enderror"
                                wire:model="nama" placeholder="Nama Siswa"
                                wire:loading.attr="disabled" wire:target="save">
                            @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-2">
                            <label>Kelas</label>
                            <select class="form-control @error('kelasId') is-invalid @enderror"
                                wire:model="kelasId"
                                wire:loading.attr="disabled" wire:target="save">
                                <option value="">Pilih kelas</option>
                                @foreach ($kelasOptions as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                                @endforeach
                            </select>
                            @error('kelasId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-2 d-flex align-items-end pb-1">
                            <button type="submit" class="btn btn-primary siswa-btn mr-2"
                                wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fas fa-{{ $isEditing ? 'save' : 'plus' }} mr-1"></i>
                                    {{ $isEditing ? 'Simpan Edit' : 'Tambah Siswa' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan
                                </span>
                            </button>

                            @if ($isEditing)
                                <button type="button" class="btn btn-outline-secondary siswa-btn"
                                    wire:click="cancelEdit">Batal</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @endif



        <div class="siswa-table-shell">
            <div class="siswa-table-head">
                <div>
                    <div class="siswa-table-title">Tabel Data Siswa</div>
                    <div class="siswa-table-subtitle">Menampilkan data siswa berdasarkan filter yang dipilih.</div>
                </div>
            </div>

            <div class="siswa-table-wrap">
                <div class="siswa-table-loading" wire:loading.flex wire:target="filterKelasNama,search">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Memuat data siswa...
                </div>
                <livewire:siswa.siswa-table :key="'siswa-table'" />
            </div>
        </div>
    </div>
</div>
