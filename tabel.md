# Konteks Tabel Database

Dokumen ini menjelaskan tabel yang sudah ada pada proyek dan tabel baru yang ditambahkan untuk kebutuhan data sekolah, kelas, siswa, tahun ajar, dan penilaian.

## Tabel Bawaan Laravel

### users

Menyimpan akun pengguna aplikasi.

- `id`: primary key.
- `name`: nama pengguna.
- `email`: email pengguna, unik.
- `email_verified_at`: waktu verifikasi email, nullable.
- `password`: password pengguna yang disimpan dalam bentuk hash.
- `remember_token`: token untuk fitur remember me.
- `created_at`, `updated_at`: timestamp Laravel.

- `sekolah_id`: foreign key nullable ke `sekolah`. User admin tidak wajib memiliki data sekolah.

Relasi:

- Belongs-to nullable dengan `sekolah`. Banyak user/guru dapat terhubung ke satu sekolah.
- Terhubung ke tabel role dan permission melalui package Spatie Laravel Permission.

### password_reset_tokens

Menyimpan token reset password berdasarkan email.

- `email`: primary key.
- `token`: token reset password.
- `created_at`: waktu token dibuat.

### sessions

Menyimpan data sesi pengguna.

- `id`: primary key sesi.
- `user_id`: ID user yang sedang login, nullable.
- `ip_address`: alamat IP pengguna.
- `user_agent`: informasi browser/perangkat.
- `payload`: isi data sesi.
- `last_activity`: waktu aktivitas terakhir.

### cache

Menyimpan data cache aplikasi.

- `key`: primary key cache.
- `value`: isi cache.
- `expiration`: waktu kedaluwarsa cache.

### cache_locks

Menyimpan lock untuk mekanisme atomic cache lock.

- `key`: primary key lock.
- `owner`: pemilik lock.
- `expiration`: waktu kedaluwarsa lock.

### jobs

Menyimpan antrean job Laravel yang belum diproses.

- `id`: primary key.
- `queue`: nama queue.
- `payload`: data job.
- `attempts`: jumlah percobaan eksekusi.
- `reserved_at`: waktu job sedang diambil worker.
- `available_at`: waktu job tersedia untuk diproses.
- `created_at`: waktu job dibuat.

### job_batches

Menyimpan metadata batch job.

- `id`: primary key batch.
- `name`: nama batch.
- `total_jobs`: jumlah seluruh job.
- `pending_jobs`: jumlah job yang belum selesai.
- `failed_jobs`: jumlah job gagal.
- `failed_job_ids`: daftar ID job yang gagal.
- `options`: opsi batch, nullable.
- `cancelled_at`: waktu batch dibatalkan.
- `created_at`: waktu batch dibuat.
- `finished_at`: waktu batch selesai.

### failed_jobs

Menyimpan job yang gagal dieksekusi.

- `id`: primary key.
- `uuid`: UUID job, unik.
- `connection`: koneksi queue.
- `queue`: nama queue.
- `payload`: data job.
- `exception`: detail error.
- `failed_at`: waktu job gagal.

## Tabel Spatie Laravel Permission

### permissions

Menyimpan daftar permission aplikasi.

- `id`: primary key.
- `name`: nama permission.
- `guard_name`: guard Laravel yang digunakan.
- `created_at`, `updated_at`: timestamp Laravel.

### roles

Menyimpan daftar role aplikasi.

- `id`: primary key.
- `name`: nama role.
- `guard_name`: guard Laravel yang digunakan.
- `created_at`, `updated_at`: timestamp Laravel.

### model_has_permissions

Tabel pivot polymorphic untuk memberi permission langsung ke model, termasuk `User`.

- `permission_id`: foreign key ke `permissions`.
- `model_type`: class model penerima permission.
- `model_id`: ID model penerima permission.

### model_has_roles

Tabel pivot polymorphic untuk memberi role ke model, termasuk `User`.

- `role_id`: foreign key ke `roles`.
- `model_type`: class model penerima role.
- `model_id`: ID model penerima role.

### role_has_permissions

Tabel pivot untuk menghubungkan role dengan permission.

- `permission_id`: foreign key ke `permissions`.
- `role_id`: foreign key ke `roles`.

## Tabel Baru

### sekolah

Menyimpan data sekolah. Tabel ini berhubungan one-to-one dengan `users`, tetapi relasinya nullable karena user admin tidak wajib memiliki data sekolah.

- `id`: primary key.
- `nama`: nama sekolah, unik.
- `alamat`: alamat sekolah.
- `created_at`, `updated_at`: timestamp Laravel.

Relasi:

- Has-many `users`.
- Has-many `kelas`.

### tahun_ajar

Menyimpan periode tahun ajar dan semester.

- `id`: primary key.
- `nama`: nama tahun ajar, berisi informasi tahun dan semester, misalnya `2025/2026 Ganjil`.
- `created_at`, `updated_at`: timestamp Laravel.

Relasi:

- Has-many `kelas`.

### kelas

Menyimpan data kelas pada sekolah dan tahun ajar tertentu.

- `id`: primary key.
- `sekolah_id`: foreign key ke `sekolah`.
- `tahun_ajar_id`: foreign key ke `tahun_ajar`.
- `nama`: nama kelas, maksimal 5 karakter.
- `created_at`, `updated_at`: timestamp Laravel.

Relasi:

- Belongs-to `sekolah`.
- Belongs-to `tahun_ajar`.
- Has-many `siswa`.

### siswa

Menyimpan data siswa dalam suatu kelas.

- `id`: primary key.
- `kelas_id`: foreign key ke `kelas`.
- `nama`: nama siswa.
- `gender`: enum `l` atau `p`.
- `rata_poin`: nilai float yang merupakan hasil perhitungan poin rata-rata dari `penilaian` pada pertemuan yang sudah berjalan.
- `created_at`, `updated_at`: timestamp Laravel.

Relasi:

- Belongs-to `kelas`.
- Has-many `penilaian`.

### penilaian

Menyimpan data penilaian siswa per pertemuan.

- `id`: primary key.
- `siswa_id`: foreign key ke `siswa`.
- `pertemuan`: enum `1` sampai `16`.
- `level`: enum `0` sampai `5`.
- `created_at`, `updated_at`: timestamp Laravel.

Relasi:

- Belongs-to `siswa`.
