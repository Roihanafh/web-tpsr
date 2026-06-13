<div class="tahun-ajar-page">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="tahun-ajar-panel">
        <div class="tahun-ajar-toolbar">
            <div class="tahun-ajar-toolbar-filters">
                <button type="button" class="btn @if($loadData) btn-outline-info @else btn-info @endif tahun-ajar-btn" wire:click="tampilkanData" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="tampilkanData">
                        @if($loadData)
                            <i class="fas fa-sync-alt mr-1"></i>
                            Reload Data
                        @else
                            <i class="fas fa-eye mr-1"></i>
                            Tampilkan Data
                        @endif
                    </span>
                    <span wire:loading wire:target="tampilkanData">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Memuat...
                    </span>
                </button>
            </div>

            <div class="tahun-ajar-actions">
                <div class="tahun-ajar-search-wrap">
                    <span class="tahun-ajar-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control tahun-ajar-search"
                        placeholder="Cari..."
                        wire:model.live.debounce.300ms="search"
                    >
                </div>

                <button type="button" class="btn btn-primary tahun-ajar-btn" wire:click="toggleForm" wire:loading.attr="disabled">
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Tahun Ajar
                </button>
            </div>
        </div>

        @if ($showForm)
            <div class="tahun-ajar-form-grid position-relative mt-3">
                <div class="tahun-ajar-loading-layer" wire:loading.flex wire:target="save,edit,cancelEdit">
                    <div class="tahun-ajar-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="save" class="tahun-ajar-form">
                    <div class="form-row">
                        @if ($isEditing)
                            <div class="col-md-8 mb-2">
                                <label for="nama">Nama Tahun Ajaran / Semester</label>
                                <input id="nama" type="text" class="form-control @error('nama') is-invalid @enderror" wire:model="nama" placeholder="2025/2026 Ganjil" wire:loading.attr="disabled" wire:target="save">
                                @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="col-md-8 mb-2">
                                <label for="tahun">Tahun Ajaran baru</label>
                                <input id="tahun" type="text" class="form-control @error('tahun') is-invalid @enderror" wire:model="tahun" placeholder="Contoh: 2025/2026" wire:loading.attr="disabled" wire:target="save">
                                @error('tahun') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="col-md-4 mb-2 tahun-ajar-submit">
                            <button type="submit" class="btn btn-primary tahun-ajar-btn" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fas fa-save mr-1"></i>
                                    {{ $isEditing ? 'Simpan Edit' : 'Simpan Baru' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Menyimpan
                                </span>
                            </button>

                            <button type="button" class="btn btn-outline-secondary tahun-ajar-btn" wire:click="cancelEdit" wire:loading.attr="disabled" wire:target="cancelEdit,save">
                                <span>Batal</span>
                            </button>
                        </div>
                    </div>
                    @if (!$isEditing)
                        <div class="form-row mt-n1">
                            <div class="col-md-8">
                                <small class="text-muted d-block mb-2">Membuat 2 record sekaligus untuk semester Ganjil dan Genap.</small>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        @endif

        <div class="tahun-ajar-table-shell">
            <div class="tahun-ajar-table-head">
                <div>
                    <div class="tahun-ajar-table-title">Tabel Data Tahun Ajaran</div>
                    <div class="tahun-ajar-table-subtitle">Menampilkan daftar tahun ajaran dan semester aktif.</div>
                </div>
            </div>

            <div class="tahun-ajar-table-wrap" style="min-height: 150px;">
                <div class="tahun-ajar-table-loading" wire:loading.flex wire:target="search, tampilkanData">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Memuat data...
                </div>
                @if ($loadData)
                    <livewire:admin.tahun-ajar-table :key="'tahun-ajar-table-'.$search" />
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-secondary">Data belum ditampilkan</h5>
                        <p class="text-muted font-weight-normal">Klik tombol "Tampilkan Data" untuk memuat daftar tahun ajaran dari database.</p>
                        <button type="button" class="btn btn-primary mt-2" wire:click="tampilkanData" wire:loading.attr="disabled">
                            <i class="fas fa-eye mr-1"></i> Tampilkan Data
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
