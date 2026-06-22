<div class="users-page">
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

    <div class="users-panel">
        <div class="users-toolbar">
            <div class="users-actions">
                <div class="users-search-wrap">
                    <span class="users-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control users-search"
                        placeholder="Cari..."
                        wire:model.live.debounce.300ms="search"
                    >
                </div>

                <button type="button" class="btn btn-primary users-btn" wire:click="toggleForm" wire:loading.attr="disabled">
                    <i class="fas fa-user-plus mr-1"></i>
                    Tambah User
                </button>
            </div>
        </div>

        @if ($showForm)
            <div class="users-form-grid position-relative mt-3">
                <div class="users-loading-layer" wire:loading.flex wire:target="save,edit,cancelEdit">
                    <div class="users-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="save" class="users-form">
                    <div class="form-row">
                        <div class="col-md-3 mb-2">
                            <label for="name">Nama Lengkap</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Nama Lengkap" wire:loading.attr="disabled" wire:target="save">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-3 mb-2">
                            <label for="email">Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="Email" wire:loading.attr="disabled" wire:target="save">
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2 mb-2">
                            <label for="password">Password {{ $isEditing ? '(Opsional)' : '' }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password" placeholder="Password" wire:loading.attr="disabled" wire:target="save">
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2 mb-2">
                            <label for="role">Role</label>
                            <select id="role" class="form-control @error('role') is-invalid @enderror" wire:model="role" wire:loading.attr="disabled" wire:target="save">
                                <option value="guru">GURU</option>
                                <option value="admin">ADMIN</option>
                            </select>
                            @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2 mb-2">
                            <label for="sekolahId">Sekolah</label>
                            <select id="sekolahId" class="form-control @error('sekolahId') is-invalid @enderror" wire:model="sekolahId" wire:loading.attr="disabled" wire:target="save">
                                <option value="">Tanpa Sekolah / Admin</option>
                                @foreach ($sekolahOptions as $sekolah)
                                    <option value="{{ $sekolah->id }}">{{ $sekolah->nama }}</option>
                                @endforeach
                            </select>
                            @error('sekolahId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="submit" class="btn btn-primary users-btn" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save mr-1"></i>
                                Simpan
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Menyimpan
                            </span>
                        </button>

                        <button type="button" class="btn btn-outline-secondary users-btn" wire:click="cancelEdit" wire:loading.attr="disabled" wire:target="cancelEdit,save">
                            <span>Batal</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="users-table-shell">
            <div class="users-table-head">
                <div>
                    <div class="users-table-title">Tabel Data User</div>
                    <div class="users-table-subtitle">Menampilkan daftar user dan administrator sistem.</div>
                </div>
            </div>

            <div class="users-table-wrap" style="min-height: 150px;">
                <div class="users-table-loading" wire:loading.flex wire:target="search">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Memuat data...
                </div>
                <livewire:admin.user-table :key="'user-table'" />
            </div>
        </div>
    </div>
</div>
