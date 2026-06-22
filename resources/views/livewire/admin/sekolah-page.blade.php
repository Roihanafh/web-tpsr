<div class="sekolah-page">
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

    <div class="sekolah-panel">
        <div class="sekolah-toolbar">
            <div class="sekolah-actions">
                <div class="sekolah-search-wrap">
                    <span class="sekolah-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control sekolah-search"
                        placeholder="Cari..."
                        wire:model.live.debounce.300ms="search"
                    >
                </div>

                <button type="button" class="btn btn-primary sekolah-btn" wire:click="toggleForm" wire:loading.attr="disabled">
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Sekolah
                </button>
            </div>
        </div>

        @if ($showForm)
            <div class="sekolah-form-grid position-relative mt-3">
                <div class="sekolah-loading-layer" wire:loading.flex wire:target="save,edit,cancelEdit">
                    <div class="sekolah-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="save" class="sekolah-form">
                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <label for="nama">Nama Sekolah</label>
                            <input id="nama" type="text" class="form-control @error('nama') is-invalid @enderror" wire:model="nama" placeholder="Masukkan nama sekolah" wire:loading.attr="disabled" wire:target="save">
                            @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-2">
                            <label for="alamat">Alamat Sekolah</label>
                            <input id="alamat" type="text" class="form-control @error('alamat') is-invalid @enderror" wire:model="alamat" placeholder="Masukkan alamat sekolah" wire:loading.attr="disabled" wire:target="save">
                            @error('alamat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-2 sekolah-submit">
                            <button type="submit" class="btn btn-primary sekolah-btn" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fas fa-save mr-1"></i>
                                    Simpan
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Simpan
                                </span>
                            </button>

                            <button type="button" class="btn btn-outline-secondary sekolah-btn" wire:click="cancelEdit" wire:loading.attr="disabled" wire:target="cancelEdit,save">
                                <span>Batal</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <div class="sekolah-table-shell">
            <div class="sekolah-table-head">
                <div>
                    <div class="sekolah-table-title">Tabel Data Sekolah</div>
                    <div class="sekolah-table-subtitle">Menampilkan daftar sekolah yang terdaftar di sistem.</div>
                </div>
            </div>

            <div class="sekolah-table-wrap" style="min-height: 150px;">
                <div class="sekolah-table-loading" wire:loading.flex wire:target="search">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Memuat data...
                </div>
                <livewire:admin.sekolah-table :key="'sekolah-table'" />
            </div>
        </div>
    </div>

    <!-- Bootstrap 4 Modal - Detail Guru -->
    <div class="modal fade" id="teachersModal" tabindex="-1" role="dialog" aria-labelledby="teachersModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="teachersModalLabel">Daftar Guru / User: <strong>{{ $selectedSekolahNama }}</strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
                    @if (count($teachersList) === 0)
                        <div class="alert alert-info text-center">
                            Tidak ada guru/user yang terdaftar di sekolah ini.
                        </div>
                    @else
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teachersList as $index => $teacher)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $teacher['name'] }}</td>
                                        <td>{{ $teacher['email'] }}</td>
                                        <td>
                                            <span class="badge badge-success">{{ $teacher['roles'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.addEventListener('open-teachers-modal', function() {
                    $('#teachersModal').modal('show');
                });
            });
        </script>
    @endpush
</div>
