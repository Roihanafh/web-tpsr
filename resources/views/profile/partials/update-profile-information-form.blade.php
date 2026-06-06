<p class="text-muted">
    Perbarui informasi nama dan email akun Anda.
</p>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="form-group">
        <label for="name">Nama</label>
        <input
            id="name"
            name="name"
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $user->name) }}"
            required
            autofocus
            autocomplete="name"
        >
        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email) }}"
            required
            autocomplete="username"
        >
        @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success py-2">
            Profil berhasil diperbarui.
        </div>
    @endif

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i>Simpan
    </button>
</form>
