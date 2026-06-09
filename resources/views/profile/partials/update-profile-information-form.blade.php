<p class="text-muted">
    Perbarui informasi nama, email, dan sekolah akun Anda.
</p>

<form id="profileInformationForm" method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')
    <input id="sekolah_action" name="sekolah_action" type="hidden" value="move">

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

    <div class="form-group">
        <label for="nama_sekolah">Nama Sekolah</label>
        <input
            id="nama_sekolah"
            name="nama_sekolah"
            type="text"
            class="form-control @error('nama_sekolah') is-invalid @enderror"
            value="{{ old('nama_sekolah', $user->sekolah?->nama) }}"
            autocomplete="organization"
        >
        @error('nama_sekolah')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="alamat_sekolah">Alamat Sekolah</label>
        <textarea
            id="alamat_sekolah"
            name="alamat_sekolah"
            class="form-control @error('alamat_sekolah') is-invalid @enderror"
            rows="3"
            autocomplete="street-address"
        >{{ old('alamat_sekolah', $user->sekolah?->alamat) }}</textarea>
        @error('alamat_sekolah')
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

<div class="modal fade" id="schoolConflictModal" tabindex="-1" role="dialog" aria-labelledby="schoolConflictModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="schoolConflictModalLabel">Konfirmasi Sekolah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p>
                    Sekolah <strong id="matchedSchoolName"></strong> sudah ada di database.
                </p>
                <p>
                    Nama sekolah tidak dapat dibuat ganda. Gunakan data sekolah yang sudah ada untuk berpindah ke sekolah tersebut.
                </p>
                <p id="matchedSchoolAddress" class="text-muted mb-0"></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="moveSchoolButton">
                    Pindah Sekolah
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('profileInformationForm');
        const schoolNameInput = document.getElementById('nama_sekolah');
        const schoolActionInput = document.getElementById('sekolah_action');
        const matchedSchoolName = document.getElementById('matchedSchoolName');
        const matchedSchoolAddress = document.getElementById('matchedSchoolAddress');
        const moveSchoolButton = document.getElementById('moveSchoolButton');
        const currentSchoolId = @json($user->sekolah?->id);
        const schools = @json($sekolahOptions);
        let confirmedSchoolAction = false;

        const normalizeSchoolName = function (value) {
            return value.trim().toLocaleLowerCase();
        };

        const submitWithAction = function (action) {
            confirmedSchoolAction = true;
            schoolActionInput.value = action;
            form.submit();
        };

        form.addEventListener('submit', function (event) {
            if (confirmedSchoolAction) {
                return;
            }

            const schoolName = normalizeSchoolName(schoolNameInput.value);
            const matchedSchool = schools.find(function (school) {
                return normalizeSchoolName(school.nama) === schoolName && school.id !== currentSchoolId;
            });

            if (! matchedSchool) {
                return;
            }

            event.preventDefault();
            matchedSchoolName.textContent = matchedSchool.nama;
            matchedSchoolAddress.textContent = matchedSchool.alamat || 'Alamat belum diisi.';
            $('#schoolConflictModal').modal('show');
        });

        moveSchoolButton.addEventListener('click', function () {
            submitWithAction('move');
        });
    });
</script>
