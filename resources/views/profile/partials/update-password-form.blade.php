<p class="text-muted">
    Gunakan password yang kuat untuk menjaga keamanan akun.
</p>

<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="form-group">
        <label for="update_password_current_password">Password Saat Ini</label>
        <input
            id="update_password_current_password"
            name="current_password"
            type="password"
            class="form-control @if ($errors->updatePassword->has('current_password')) is-invalid @endif"
            autocomplete="current-password"
        >
        @if ($errors->updatePassword->has('current_password'))
            <span class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</span>
        @endif
    </div>

    <div class="form-group">
        <label for="update_password_password">Password Baru</label>
        <input
            id="update_password_password"
            name="password"
            type="password"
            class="form-control @if ($errors->updatePassword->has('password')) is-invalid @endif"
            autocomplete="new-password"
        >
        @if ($errors->updatePassword->has('password'))
            <span class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</span>
        @endif
    </div>

    <div class="form-group">
        <label for="update_password_password_confirmation">Konfirmasi Password Baru</label>
        <input
            id="update_password_password_confirmation"
            name="password_confirmation"
            type="password"
            class="form-control @if ($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
            autocomplete="new-password"
        >
        @if ($errors->updatePassword->has('password_confirmation'))
            <span class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</span>
        @endif
    </div>

    @if (session('status') === 'password-updated')
        <div class="alert alert-success py-2">
            Password berhasil diperbarui.
        </div>
    @endif

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-key mr-1"></i>Ubah Password
    </button>
</form>
