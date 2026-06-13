# Panduan Deployment: Railway & Aiven MySQL Database

Dokumentasi ini memberikan panduan langkah demi langkah untuk men-deploy aplikasi Laravel 12 (TPSR) ke platform **Railway** dengan menggunakan database cloud **Aiven MySQL**.

---

## 📋 Daftar Isi
1. [Langkah 1: Setup Aiven MySQL Database](#langkah-1-setup-aiven-mysql-database)
2. [Langkah 2: Mempersiapkan Kode & Git](#langkah-2-mempersiapkan-kode--git)
3. [Langkah 3: Deploy ke Railway](#langkah-3-deploy-ke-railway)
4. [Langkah 4: Setup Environment Variables di Railway](#langkah-4-setup-environment-variables-di-railway)
5. [Langkah 5: Inisialisasi Database (Seeding)](#langkah-5-inisialisasi-database-seeding)
6. [Troubleshooting & Tips](#troubleshooting--tips)

---

## 🛠️ Langkah 1: Setup Aiven MySQL Database

Aiven adalah penyedia database cloud yang andal dan aman. Secara default, Aiven mewajibkan koneksi SSL untuk mengakses database.

1. **Daftar / Login ke Aiven:**
   - Kunjungi [Aiven.io](https://aiven.io) dan buat akun (tersedia free trial).
2. **Buat Layanan MySQL Baru:**
   - Klik **Create Service**.
   - Pilih **MySQL** sebagai jenis database.
   - Pilih Cloud Provider (contoh: *Google Cloud* atau *AWS*) dan pilih lokasi server terdekat (contoh: *singapore* untuk latensi terbaik dari Indonesia).
   - Pilih Service Plan (pilih plan gratis/hobbyist untuk uji coba).
   - Beri nama layanan Anda (misal: `tpsr-mysql-db`), lalu klik **Create Service**.
3. **Dapatkan Detail Koneksi (Connection Information):**
   - Tunggu status layanan berubah dari *Rebuilding* menjadi *Running* (biasanya 2-5 menit).
   - Di tab **Overview**, cari bagian **Connection Information**. Catat informasi berikut:
     - **Host**: (Contoh: `tpsr-mysql-db-myproject.aivencloud.com`)
     - **Port**: (Contoh: `12345` - *Aiven menggunakan port custom*)
     - **User**: (Default: `avnadmin`)
     - **Password**: (Klik ikon mata untuk melihat password acak yang dibuat)
     - **Database**: (Default: `defaultdb`)
4. **Unduh CA Certificate:**
   - Pada panel *Connection Information*, klik **Download CA Certificate** (atau salin isi teksnya). Simpan isi teks ini karena akan dimasukkan ke variabel lingkungan (`AIVEN_CA_CERT`) di Railway.

---

## 💻 Langkah 2: Mempersiapkan Kode & Git

Pastikan file konfigurasi Docker yang baru dibuat sudah masuk ke dalam Git repository Anda.

1. **Commit file baru ke Git local:**
   Buka terminal di root project Anda dan jalankan perintah berikut:
   ```bash
   git add Dockerfile .dockerignore docker/
   git commit -m "feat: add docker configuration for deployment"
   ```
2. **Push ke GitHub:**
   Pastikan Anda sudah menghubungkan project local Anda ke GitHub:
   ```bash
   # Jika belum meng-push ke branch utama (misal: main atau master)
   git push origin main
   ```

---

## 🚀 Langkah 3: Deploy ke Railway

Railway akan membaca `Dockerfile` di root project secara otomatis untuk mem-build container aplikasi Anda.

1. **Login ke Railway:**
   - Masuk ke [Railway.app](https://railway.app) menggunakan akun GitHub Anda.
2. **Buat Project Baru:**
   - Klik tombol **New Project** di pojok kanan atas.
   - Pilih **Deploy from GitHub repo**.
   - Pilih repository project Anda (`web-tpsr`).
3. **Konfigurasi Cabang (Branch):**
   - Pilih branch yang ingin dideploy (contoh: `main`).
   - Klik **Deploy Now**.
   - Deployment pertama akan gagal atau tertunda sejenak karena kita belum memasukkan environment variables. Jangan khawatir, kita akan mengaturnya di langkah berikutnya.

---

## 🔑 Langkah 4: Setup Environment Variables di Railway

Railway memerlukan variabel lingkungan agar Laravel dapat berjalan dengan benar dan terhubung ke Aiven MySQL dengan aman menggunakan SSL.

1. Masuk ke halaman project Anda di Railway.
2. Klik pada **Service** aplikasi Anda, lalu pilih tab **Variables**.
3. Klik **New Variable** (atau **Raw Editor** untuk menambahkan sekaligus) dan masukkan variabel-variabel berikut:

### ⚙️ Konfigurasi Aplikasi (Laravel Core)
| Variable | Value | Keterangan |
|---|---|---|
| `APP_NAME` | `TPSR Web` | Nama aplikasi Anda |
| `APP_ENV` | `production` | Wajib diatur ke production |
| `APP_DEBUG` | `false` | Mematikan debug mode demi keamanan |
| `APP_KEY` | `base64:xxxx...` | Salin kunci aplikasi Anda dari file `.env` lokal |
| `APP_URL` | `${{RAILWAY_PUBLIC_DOMAIN}}` | Railway akan otomatis mengisi ini dengan domain publik Anda |

### 🗄️ Konfigurasi Database (Koneksi Aiven MySQL + SSL)
| Variable | Value | Keterangan |
|---|---|---|
| `DB_CONNECTION` | `mysql` | Menggunakan MySQL driver |
| `DB_HOST` | *(Host dari Aiven)* | Contoh: `tpsr-mysql-...aivencloud.com` |
| `DB_PORT` | *(Port dari Aiven)* | Contoh: `25482` |
| `DB_DATABASE` | `defaultdb` | Nama database default Aiven |
| `DB_USERNAME` | `avnadmin` | Username default Aiven |
| `DB_PASSWORD` | *(Password dari Aiven)* | Password unik yang tertera di Aiven |
| `AIVEN_CA_CERT` | *(Salin seluruh isi file CA Certificate)* | Tempel seluruh teks sertifikat `ca.pem` dari Aiven di sini |
| `RUN_MIGRATIONS` | `true` | Menjalankan perintah `php artisan migrate --force` saat aplikasi dijalankan |
| `RUN_SEEDER` | `true` | Menjalankan perintah `php artisan db:seed --force` (set ke `true` saat pertama kali deploy, setelah data masuk sebaiknya ubah ke `false` atau hapus variabel ini) |

> **Catatan Penting SSL:** 
> Dockerfile kita memiliki konfigurasi di `docker/entrypoint.sh` yang mendeteksi variabel `AIVEN_CA_CERT`. Jika terdeteksi, script akan otomatis membuat file `/var/www/html/certs/ca.pem` di dalam container dan mengarahkan koneksi MySQL Laravel ke sertifikat tersebut menggunakan SSL. Anda tidak perlu memasukkan sertifikat ke dalam Git!


---

## 🗃️ Langkah 5: Inisialisasi Database (Seeding)

Saat pertama kali dideploy, database di Aiven masih kosong. Meskipun tabel sudah terbuat berkat `RUN_MIGRATIONS=true`, Anda tetap memerlukan data awal seperti Roles, Permissions, dan Akun Admin/Guru agar bisa login.

Anda dapat menjalankan perintah seed melalui **Railway CLI** atau melalui terminal interaktif di dashboard web Railway.

### Cara 1: Menggunakan Terminal Dashboard Railway (Sangat Mudah)
1. Buka dashboard Railway Anda.
2. Klik service aplikasi Anda, lalu buka tab **View Logs** / **Terminal** (atau tab **Console** jika tersedia di menu samping).
3. Jika tab terminal interaktif tersedia, Anda bisa menjalankan perintah command. Jika tidak, cara paling aman dan standar adalah menggunakan **Railway CLI** dari komputer Anda sendiri untuk mengakses container yang sedang berjalan.

### Cara 2: Menggunakan Railway CLI (Rekomendasi)
1. Instal Railway CLI di komputer lokal Anda (jika belum):
   ```bash
   npm install -g @railway/cli
   ```
2. Login ke akun Railway Anda melalui terminal:
   ```bash
   railway login
   ```
3. Arahkan terminal lokal Anda ke direktori project `web-tpsr`, lalu hubungkan dengan project Railway:
   ```bash
   railway link
   ```
4. Jalankan perintah database seed langsung ke container produksi:
   ```bash
   railway run php artisan db:seed --force
   ```
   *Perintah di atas akan menjalankan `DatabaseSeeder` utama yang akan membuat roles, permissions, dan akun testing secara otomatis di database Aiven.*

---

## 🔍 Troubleshooting & Tips

### 1. File Uploads / Export Excel Terlalu Besar
Jika Anda mengunggah berkas besar atau melakukan ekspor data yang memakan waktu lama, konfigurasi Docker kita sudah disiapkan dengan:
- `client_max_body_size 64M` di Nginx.
- `upload_max_filesize = 64M` dan `post_max_size = 64M` di PHP.
- `fastcgi_read_timeout 300` di Nginx untuk mencegah error Gateway Timeout (504).

### 2. Error: "SQLSTATE[HY000] [2002] Connection refused" atau "Access denied"
- Pastikan status layanan database Anda di Aiven sudah **Running**.
- Periksa kembali apakah Host, Port, Username, dan Password yang Anda masukkan di tab Variables Railway sudah benar.
- Pastikan variabel `AIVEN_CA_CERT` disalin dengan benar (termasuk baris `-----BEGIN CERTIFICATE-----` dan `-----END CERTIFICATE-----`).

### 3. Cara Mengubah Role / Menambah User Baru di Produksi
Jika Anda ingin menambah user baru atau mereset password di server produksi, gunakan Railway CLI untuk masuk ke Tinker:
```bash
railway run php artisan tinker
```
Di dalam Tinker console:
```php
use App\Models\User;
$user = User::create([
    'name' => 'Admin Baru',
    'email' => 'admin.baru@example.com',
    'password' => bcrypt('password_aman_anda'),
]);
$user->assignRole('admin');
exit;
```
