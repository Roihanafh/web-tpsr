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
            Akun ini belum memiliki relasi sekolah. Data siswa hanya bisa ditambah, diimport, dan diexport oleh user yang terhubung dengan sekolah.
        </div>
    @endunless

    <div class="siswa-panel">
        <div class="siswa-toolbar">
            <div class="siswa-toolbar-filters">
                <select class="form-control siswa-select" wire:model.live="filterKelasNama">
                    <option value="">Kelas</option>
                    @foreach ($filterKelasOptions as $kelasNama)
                        <option value="{{ $kelasNama }}">{{ $kelasNama }}</option>
                    @endforeach
                </select>

                <select class="form-control siswa-select" wire:model.live="filterTahunAjarId">
                    <option value="0">Tahun Ajaran</option>
                    @foreach ($tahunAjarOptions as $tahunAjar)
                        <option value="{{ $tahunAjar->id }}">{{ $tahunAjar->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="siswa-actions">
                <div class="siswa-search-wrap">
                    <span class="siswa-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control siswa-search"
                        placeholder="Search"
                        wire:model.live.debounce.300ms="search"
                    >
                </div>

                <button type="button" class="btn btn-danger siswa-btn" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                    <span wire:loading.remove wire:target="export">
                        <i class="fas fa-file-export mr-1"></i>
                        Export
                    </span>
                    <span wire:loading wire:target="export">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Exporting
                    </span>
                </button>

                <button type="button" class="btn btn-success siswa-btn" wire:click="toggleImportForm" wire:loading.attr="disabled">
                    <i class="fas fa-plus mr-1"></i>
                    Import Excel
                </button>

                <button type="button" class="btn btn-primary siswa-btn" wire:click="toggleForm" wire:loading.attr="disabled">
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Siswa
                </button>
            </div>
        </div>

        @if ($showForm)
            <div class="siswa-form-grid position-relative mt-3">
                <div class="siswa-loading-layer" wire:loading.flex wire:target="save,edit,cancelEdit">
                    <div class="siswa-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="save" class="siswa-form">
                    <div class="form-row">
                        <div class="col-md-3 mb-2">
                            <label for="tahunAjarId">Tahun Ajaran</label>
                            <select id="tahunAjarId" class="form-control @error('tahunAjarId') is-invalid @enderror" wire:model.live="tahunAjarId" wire:loading.attr="disabled" wire:target="save,edit">
                                <option value="">Pilih tahun ajaran</option>
                                @foreach ($tahunAjarOptions as $tahunAjar)
                                    <option value="{{ $tahunAjar->id }}">{{ $tahunAjar->nama }}</option>
                                @endforeach
                            </select>
                            @error('tahunAjarId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-3 mb-2">
                            <label for="kelasId">Kelas</label>
                            <select id="kelasId" class="form-control @error('kelasId') is-invalid @enderror" wire:model="kelasId" wire:loading.attr="disabled" wire:target="save,edit,tahunAjarId" @disabled(! $tahunAjarId)>
                                <option value="">Pilih kelas</option>
                                @foreach ($kelasOptions as $kelas)
                                    <option value="{{ data_get($kelas, 'id') }}">{{ data_get($kelas, 'nama') }}</option>
                                @endforeach
                            </select>
                            @error('kelasId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2 mb-2">
                            <label for="nama">Nama Siswa</label>
                            <input id="nama" type="text" maxlength="100" class="form-control @error('nama') is-invalid @enderror" wire:model="nama" placeholder="Nama Siswa" wire:loading.attr="disabled" wire:target="save,edit">
                            @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2 mb-2">
                            <label for="gender">Jenis Kelamin</label>
                            <select id="gender" wire:key="gender-{{ $siswaId ?? 'new' }}" class="form-control @error('gender') is-invalid @enderror" wire:model="gender" wire:loading.attr="disabled" wire:target="save,edit">
                                <option value="">Pilih jenis kelamin</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            @error('gender') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2 mb-2 siswa-submit">
                            <button type="submit" class="btn btn-primary siswa-btn" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fas fa-plus mr-1"></i>
                                    {{ $isEditing ? 'Simpan Edit' : 'Tambah Siswa' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Menyimpan
                                </span>
                            </button>

                            @if ($isEditing)
                                <button type="button" class="btn btn-outline-secondary siswa-btn" wire:click="cancelEdit" wire:loading.attr="disabled" wire:target="cancelEdit,save">
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
            <div class="siswa-form-grid position-relative mt-3">
                <div class="siswa-loading-layer" wire:loading.flex wire:target="import,downloadTemplate">
                    <div class="siswa-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="import" class="siswa-import">
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

        <div class="siswa-table-shell">
            <div class="siswa-table-head">
                <div>
                    <div class="siswa-table-title">Tabel Data Siswa</div>
                    <div class="siswa-table-subtitle">Menampilkan data siswa berdasarkan filter yang dipilih.</div>
                </div>
            </div>

            <div class="siswa-table-wrap">
                <div class="siswa-table-loading" wire:loading.flex wire:target="filterKelasNama,filterTahunAjarId,search">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Memuat data siswa...
                </div>
                <livewire:siswa.siswa-table :kelas-nama="$filterKelasNama" :tahun-ajar-id="$filterTahunAjarId" :key="'siswa-table-'.$filterKelasNama.'-'.$filterTahunAjarId.'-'.$search" />
            </div>
        </div>
    </div>
</div>
