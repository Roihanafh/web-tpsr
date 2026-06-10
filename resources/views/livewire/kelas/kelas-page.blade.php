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
            Akun ini belum memiliki relasi sekolah. Data kelas hanya bisa ditambah, diimport, dan diexport oleh user yang terhubung dengan sekolah.
        </div>
    @endunless

    <div class="kelas-panel">
        <div class="kelas-toolbar">
            <div class="kelas-toolbar-filters">
                <select class="form-control kelas-select" wire:model.live="filterTahunAjarId">
                    <option value="">Pilih Tahun Ajaran</option>
                    <option value="0">Semua Tahun Ajaran</option>
                    @foreach ($tahunAjarOptions as $tahunAjar)
                        <option value="{{ $tahunAjar->id }}">{{ $tahunAjar->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="kelas-actions">
                <div class="kelas-search-wrap">
                    <span class="kelas-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control kelas-search"
                        placeholder="Search"
                        wire:model.live.debounce.300ms="search"
                    >
                </div>

                <button type="button" class="btn btn-danger kelas-btn" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                    <span wire:loading.remove wire:target="export">
                        <i class="fas fa-file-export mr-1"></i>
                        Export
                    </span>
                    <span wire:loading wire:target="export">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Exporting
                    </span>
                </button>

                <button type="button" class="btn btn-success kelas-btn" wire:click="toggleImportForm" wire:loading.attr="disabled">
                    <i class="fas fa-plus mr-1"></i>
                    Import Excel
                </button>

                <button type="button" class="btn btn-primary kelas-btn" wire:click="toggleForm" wire:loading.attr="disabled">
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Kelas
                </button>
            </div>
        </div>

        @if ($showForm)
            <div class="kelas-form-grid position-relative mt-3">
                <div class="kelas-loading-layer" wire:loading.flex wire:target="save,edit,cancelEdit">
                    <div class="kelas-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="save" class="kelas-form">
                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <label for="tahunAjarId">Tahun Ajaran</label>
                            <select id="tahunAjarId" class="form-control @error('tahunAjarId') is-invalid @enderror" wire:model="tahunAjarId" wire:loading.attr="disabled" wire:target="save,edit">
                                <option value="">Pilih tahun ajaran</option>
                                @foreach ($tahunAjarOptions as $tahunAjar)
                                    <option value="{{ $tahunAjar->id }}">{{ $tahunAjar->nama }}</option>
                                @endforeach
                            </select>
                            @error('tahunAjarId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-2">
                            <label for="nama">Nama Kelas</label>
                            <input id="nama" type="text" maxlength="5" class="form-control @error('nama') is-invalid @enderror" wire:model="nama" placeholder="5-A" wire:loading.attr="disabled" wire:target="save,edit">
                            @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-2 kelas-submit">
                            <button type="submit" class="btn btn-primary kelas-btn" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fas fa-plus mr-1"></i>
                                    {{ $isEditing ? 'Simpan Edit' : 'Tambah Kelas' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Menyimpan
                                </span>
                            </button>

                            @if ($isEditing)
                                <button type="button" class="btn btn-outline-secondary kelas-btn" wire:click="cancelEdit" wire:loading.attr="disabled" wire:target="cancelEdit,save">
                                    <span wire:loading.remove wire:target="cancelEdit">Batal</span>
                                    <span wire:loading wire:target="cancelEdit">
                                        <i class="fas fa-spinner fa-spin mr-1"></i>
                                        Batal
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @endif

        @if ($showImportForm)
            <div class="kelas-form-grid position-relative mt-3">
                <div class="kelas-loading-layer" wire:loading.flex wire:target="import,downloadTemplate">
                    <div class="kelas-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="import" class="kelas-import">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0" for="fileImport">Import Excel</label>
                        <button type="button" class="btn btn-link p-0" wire:click="downloadTemplate" wire:loading.attr="disabled" wire:target="downloadTemplate">
                            <span wire:loading.remove wire:target="downloadTemplate">Download template</span>
                            <span wire:loading wire:target="downloadTemplate">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Menyiapkan template
                            </span>
                        </button>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <select class="form-control @error('tahunAjarId') is-invalid @enderror" wire:model="tahunAjarId" wire:loading.attr="disabled" wire:target="fileImport,import">
                                <option value="">Pilih tahun ajaran</option>
                                @foreach ($tahunAjarOptions as $tahunAjar)
                                    <option value="{{ $tahunAjar->id }}">{{ $tahunAjar->nama }}</option>
                                @endforeach
                            </select>
                            @error('tahunAjarId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-8 mb-2">
                            <div class="input-group">
                                <input id="fileImport" type="file" class="form-control @error('fileImport') is-invalid @enderror" wire:model="fileImport" accept=".xlsx,.xls,.csv" wire:loading.attr="disabled" wire:target="fileImport,import">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="fileImport,import">
                                        <span wire:loading.remove wire:target="fileImport,import">
                                            <i class="fas fa-file-import mr-1"></i>
                                            Import Excel
                                        </span>
                                        <span wire:loading wire:target="fileImport,import">
                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                            Mengimport
                                        </span>
                                    </button>
                                </div>
                                @error('fileImport') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        @if ($importFailures !== [])
            <div class="alert alert-warning mt-3 mb-0">
                <strong>Data gagal masuk database:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($importFailures as $failure)
                        <li>Baris {{ $failure['line'] }} - {{ $failure['nama'] }}: {{ $failure['message'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="kelas-table-shell">
            <div class="kelas-table-head">
                <div>
                    <div class="kelas-table-title">Tabel Data Kelas</div>
                    <div class="kelas-table-subtitle">Menampilkan data kelas berdasarkan filter yang dipilih.</div>
                </div>
            </div>

            <div class="kelas-table-wrap">
                <div class="kelas-table-loading" wire:loading.flex wire:target="filterTahunAjarId,search">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Memuat data kelas...
                </div>
                <livewire:kelas.kelas-table :tahun-ajar-id="$filterTahunAjarId" :key="'kelas-table-'.$filterTahunAjarId.'-'.$search" />
            </div>
        </div>
    </div>
</div>
