<p class="text-muted">
    Menghapus akun akan menghapus data akun secara permanen. Pastikan tindakan ini benar-benar diperlukan.
</p>

@if ($errors->userDeletion->isNotEmpty())
    <div class="alert alert-danger py-2">
        Password konfirmasi hapus akun belum sesuai.
    </div>
@endif

<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteAccountModal">
    <i class="fas fa-trash-alt mr-1"></i>Hapus Akun
</button>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('profile.destroy') }}" class="modal-content">
            @csrf
            @method('delete')

            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Konfirmasi Hapus Akun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="text-muted">
                    Masukkan password Anda untuk mengonfirmasi penghapusan akun.
                </p>

                <div class="form-group mb-0">
                    <label for="delete_user_password">Password</label>
                    <input
                        id="delete_user_password"
                        name="password"
                        type="password"
                        class="form-control @if ($errors->userDeletion->has('password')) is-invalid @endif"
                        placeholder="Password"
                    >
                    @if ($errors->userDeletion->has('password'))
                        <span class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</span>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus Akun</button>
            </div>
        </form>
    </div>
</div>
