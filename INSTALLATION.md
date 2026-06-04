# Panduan Instalasi Projek TPSR

Dokumentasi lengkap untuk setup dan menjalankan project TPSR (Teknologi Penilaian Sarana Raya) dengan Laravel, AdminLTE, dan Spatie Permission.

## 📋 Daftar Isi

- [Requirements](#requirements)
- [Instalasi](#instalasi)
- [Konfigurasi Database](#konfigurasi-database)
- [Setup Project](#setup-project)
- [Menjalankan Project](#menjalankan-project)
- [Akses Aplikasi](#akses-aplikasi)
- [Struktur Project](#struktur-project)
- [Troubleshooting](#troubleshooting)

---

## Requirements

### Sistem Operasi
- Windows 10+ / macOS / Linux

### Software yang Diperlukan
- **PHP** 8.2+ (dengan extensions: PDO, MySQL, OpenSSL, BCMath)
- **Composer** (PHP Dependency Manager)
- **Node.js** 18+ & **npm**
- **MySQL** 8.0+ (atau MariaDB 10.4+)
- **Git** (opsional, tapi recommended)

### Rekomendasi
- **Visual Studio Code** dengan extensions:
  - Laravel Extension Pack
  - PHP Intelephense
  - Blade
- **Laragon** (Windows) atau **Valet** (macOS) - untuk local development

---

## Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd web-tpsr
```

### 2. Copy File Environment

```bash
cp .env.example .env
```

### 3. Install PHP Dependencies

```bash
composer install
```

### 4. Install Node Dependencies

```bash
npm install
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

---

## Konfigurasi Database

### 1. Buka File `.env`

```bash
# Di root project, buka file .env
# Cari bagian database configuration:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web-tpsr
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Sesuaikan Konfigurasi

**Untuk Laragon (Windows):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web-tpsr
DB_USERNAME=root
DB_PASSWORD=
```

**Untuk Xampp/Wamp:**
- Gunakan konfigurasi yang sama seperti Laragon

**Untuk Custom Setup:**
- Sesuaikan `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` dengan konfigurasi MySQL Anda

### 3. Buat Database (Opsional)

Jika belum ada, create database melalui phpMyAdmin atau terminal:

```bash
# Melalui MySQL CLI
mysql -u root -p
CREATE DATABASE web-tpsr;
EXIT;
```

---

## Setup Project

### 1. Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
```

### 2. Run Migrations

```bash
php artisan migrate:fresh --seed
```

Command ini akan:
- ✅ Drop semua tabel
- ✅ Jalankan semua migrations
- ✅ Seed database dengan:
  - **8 Permissions** (view_dashboard, view_classes, view_students, dll)
  - **2 Roles** (guru, admin)
  - **2 Test Users** (guru@example.com, admin@example.com)

### 3. Build Frontend Assets

```bash
npm run build
```

atau untuk development dengan hot reload:

```bash
npm run dev
```

---

## Menjalankan Project

### Option 1: Menggunakan Artisan Serve (Simple)

```bash
php artisan serve
```

Aplikasi akan accessible di: `http://localhost:8000`

### Option 2: Menggunakan Laragon (Windows Recommended)

1. Buat symbolic link di `C:\laragon\www\web-tpsr` (sudah ada)
2. Double-click `Laragon` application
3. Di Laragon, klik **Web** > cari `web-tpsr`
4. Aplikasi akan buka di browser

### Option 3: Menggunakan Valet (macOS)

```bash
cd web-tpsr
valet link
valet secure
```

Aplikasi accessible di: `https://web-tpsr.local`

---

## Akses Aplikasi

### Test User Credentials

Setelah setup selesai, ada 2 test user yang sudah dibuat:

#### 1. Guru Account
```
Email    : guru@example.com
Password : password
Role     : guru
```

**Menu yang Accessible:**
- 📊 Dashboard
- 📚 Manajemen Kelas
  - Data Kelas
  - Data Siswa
- 📋 Penilaian TPSR
  - Quick Assessment
  - Checklist Observasi
- 📈 Analisis
  - Per Siswa
  - Per Kelas
- 📄 Laporan
  - Individu
  - Kelas
- 👤 Profil

#### 2. Admin Account
```
Email    : admin@example.com
Password : password
Role     : admin
```

**Menu yang Accessible:**
- 📊 Dashboard
- 👥 Manajemen User
- ⚙️ Pengaturan

### Login

1. Buka `http://localhost:8000`
2. Klik **Login**
3. Input email & password sesuai role
4. Klik **Sign in**

---

## Struktur Project

```
web-tpsr/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controllers
│   │   └── Requests/         # Form Requests
│   ├── Models/
│   │   └── User.php          # User Model (dengan Spatie Permission)
│   └── Providers/
├── bootstrap/
├── config/
│   ├── adminlte.php          # AdminLTE Configuration
│   ├── permission.php         # Spatie Permission Config
│   └── ...
├── database/
│   ├── migrations/           # Database Migrations
│   └── seeders/
│       ├── DatabaseSeeder.php      # Main Seeder
│       ├── PermissionSeeder.php    # Permissions & Roles
│       └── UserSeeder.php          # Test Users
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── layouts/
│       ├── dashboard.blade.php
│       └── ...
├── routes/
│   ├── web.php               # Web Routes
│   └── auth.php              # Auth Routes
├── storage/
├── vendor/                   # Composer packages
├── .env                      # Environment Config
├── .env.example
├── composer.json
├── package.json
├── phpunit.xml
└── README.md
```

---

## Permissions & Roles

### Available Permissions

| Permission | Deskripsi |
|-----------|-----------|
| `view_dashboard` | Akses dashboard |
| `view_classes` | Lihat manajemen kelas |
| `view_students` | Lihat data siswa |
| `view_assessment` | Akses penilaian TPSR |
| `view_analysis` | Akses analisis |
| `view_reports` | Akses laporan |
| `manage_users` | Kelola user (Admin only) |
| `manage_settings` | Kelola settings (Admin only) |

### Available Roles

| Role | Permissions | Use Case |
|------|-----------|----------|
| **guru** | view_dashboard, view_classes, view_students, view_assessment, view_analysis, view_reports | Guru/Pendidik |
| **admin** | view_dashboard, manage_users, manage_settings | Administrator Sistem |

### Menambah User Baru

```bash
php artisan tinker
```

Kemudian di dalam tinker:

```php
use App\Models\User;

// Buat user baru
$user = User::create([
    'name' => 'Nama User',
    'email' => 'email@example.com',
    'password' => bcrypt('password'),
]);

// Assign role (pilih 'guru' atau 'admin')
$user->assignRole('guru');  // atau 'admin'

exit;
```

---

## Commands Penting

### Database Operations

```bash
# Fresh migration + seed (setup awal)
php artisan migrate:fresh --seed

# Rollback dan re-run migrations
php artisan migrate:refresh --seed

# Hanya seed tertentu
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UserSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Development Commands

```bash
# Build frontend (production)
npm run build

# Hot reload frontend (development)
npm run dev

# Serve aplikasi
php artisan serve

# Interactive shell
php artisan tinker
```

### Testing

```bash
# Run tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

---

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error: 1030 Got error..."

**Solusi:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan migrate:fresh --seed
```

### Error: "Class 'Spatie\Permission\PermissionServiceProvider' not found"

**Solusi:**
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Error: "No such table: roles"

**Solusi:**
```bash
php artisan migrate
```

### Error: "Port 8000 is already in use"

**Solusi:**
```bash
# Gunakan port berbeda
php artisan serve --port=8080

# atau di Laragon, change port di settings
```

### Error: "SQLSTATE[42S02]: Table or view not found"

**Solusi:**
```bash
php artisan migrate:fresh --seed
```

### Assets tidak terbaca (CSS/JS tidak muncul)

**Solusi:**
```bash
npm install
npm run build

# Jika masih tidak muncul:
php artisan config:clear
```

### Menu tidak sesuai role

Pastikan:
1. User sudah memiliki role yang benar
2. Role sudah memiliki permissions yang benar
3. Cache sudah di-clear

```bash
php artisan cache:clear
```

---

## Tips & Best Practices

### 1. Menggunakan Tinker untuk Testing

```bash
php artisan tinker

# Cek user dan rolenya
>>> $user = User::first();
>>> $user->roles;
>>> $user->permissions;

# Cek apakah user punya permission tertentu
>>> $user->can('view_users');

exit
```

### 2. Custom Seeder

Untuk menambah data lebih banyak, edit file seeder:

```php
// database/seeders/UserSeeder.php
$users = [
    ['name' => 'Guru 1', 'email' => 'guru1@example.com', 'role' => 'guru'],
    ['name' => 'Guru 2', 'email' => 'guru2@example.com', 'role' => 'guru'],
    ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
];

foreach ($users as $userData) {
    $user = User::factory()->create([
        'name' => $userData['name'],
        'email' => $userData['email'],
    ]);
    $user->assignRole($userData['role']);
}
```

Kemudian run:
```bash
php artisan db:seed --class=UserSeeder
```

### 3. Menambah Permission Baru

Edit `database/seeders/PermissionSeeder.php`:

```php
// Tambah permission baru
Permission::create(['name' => 'delete_students']);

// Tambah ke role
$guruRole = Role::firstOrCreate(['name' => 'guru']);
$guruRole->givePermissionTo('delete_students');
```

Kemudian jalankan:
```bash
php artisan db:seed --class=PermissionSeeder
```

### 4. Reset Semua Data ke Awal

```bash
php artisan migrate:fresh --seed
```

---

## Support & Dokumentasi

- **Laravel Documentation**: https://laravel.com/docs
- **AdminLTE Documentation**: https://adminlte.io/docs
- **Spatie Permission**: https://spatie.be/docs/laravel-permission

---

## Checklist Setup

- [ ] PHP 8.2+ installed
- [ ] Composer installed
- [ ] Node.js 18+ installed
- [ ] MySQL/MariaDB running
- [ ] Repository cloned
- [ ] `.env` file copied & configured
- [ ] `composer install` completed
- [ ] `npm install` completed
- [ ] `php artisan key:generate` executed
- [ ] Database migrations & seeds completed
- [ ] Assets built (`npm run build`)
- [ ] Server running (`php artisan serve`)
- [ ] Login berhasil dengan test users
- [ ] Menu menampilkan sesuai role

---

**Last Updated:** June 4, 2026  
**Version:** 1.0.0
