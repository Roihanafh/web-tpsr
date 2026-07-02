# TPSR - Teknologi Penilaian Sarana Raya

Aplikasi web untuk manajemen penilaian berbasis teknologi dengan role-based access control.

## 🚀 Quick Start

```bash
# 1. Clone & Install
git clone <repo-url>
cd web-tpsr
composer install
npm install

# 2. Setup Environment
cp .env.example .env
php artisan key:generate

# 3. Setup Database (MySQL harus running)
php artisan migrate:fresh --seed

# 4. Build Assets
npm run build

# 5. Run Server
php artisan serve
```

Aplikasi accessible di: **http://localhost:8000**

---

## 👥 Test Users

| Role | Email | Password |
|------|-------|----------|
| Guru | guru@example.com | password |
| Admin | admin@example.com | password |

---

## 📚 Features

### Guru Dashboard
- 📊 Dashboard dengan statistik pembelajaran
- 📚 Manajemen Kelas & Siswa
- 📋 Penilaian TPSR (Quick Assessment & Checklist)
- 📈 Analisis Per Siswa & Per Kelas
- 📄 Laporan Individu & Kelas

### Admin Dashboard
- 👥 Manajemen User
- ⚙️ Pengaturan Sistem

### Authentication
- Login/Register
- Forgot Password
- Email Verification

---

## 🛠️ Tech Stack

- **Backend:** Laravel 11
- **Frontend:** AdminLTE 3, Blade, TailwindCSS
- **Database:** MySQL 8.0+
- **Build Tool:** Vite
- **Permission:** Spatie Laravel Permission

---

## 📖 Dokumentasi Lengkap

Lihat [INSTALLATION.md](./INSTALLATION.md) untuk panduan instalasi detail.

### Quick Commands

```bash
# Development
npm run dev              # Hot reload frontend
php artisan serve       # Run server

# Production
npm run build           # Build assets
php artisan config:cache  # Cache config

# Database
php artisan migrate                    # Run migrations
php artisan migrate:fresh --seed       # Reset & seed
php artisan db:seed --class=PermissionSeeder  # Seed permissions

# Cache
php artisan cache:clear                # Clear application cache
php artisan config:clear               # Clear config cache

# Debugging
php artisan tinker                     # Interactive shell
```

---

## 📁 Struktur Database

### Tables
- `users` - User accounts
- `roles` - Role definitions (guru, admin)
- `permissions` - Permission definitions
- `role_has_permissions` - Role-Permission relations
- `model_has_roles` - User-Role relations

---

## 🔐 Permission System

### Permissions
```
✓ view_dashboard         - Akses dashboard
✓ view_classes          - Lihat manajemen kelas
✓ view_students         - Lihat data siswa
✓ view_assessment       - Akses penilaian TPSR
✓ view_analysis         - Akses analisis
✓ view_reports          - Akses laporan
✓ manage_users          - Kelola user
✓ manage_settings       - Kelola settings
```

### Menu Visibility
Menu automatically hidden/shown berdasarkan role user via `can` permission check di AdminLTE config.

---

## 📝 Project Structure

```
web-tpsr/
├── app/
│   ├── Http/Controllers/
│   ├── Models/User.php (+ HasRoles trait)
│   └── Providers/
├── config/
│   ├── adminlte.php (menu configuration)
│   ├── permission.php
│   └── auth.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── PermissionSeeder.php
│       └── UserSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php
│   │   └── dashboard.blade.php
│   └── css/js/
├── routes/
│   ├── web.php
│   └── auth.php
└── INSTALLATION.md
```

---

## 🔄 Workflow

### User Registration Flow
1. User registrasi → Email verification
2. Admin assign role (guru/admin)
3. User login → Role-based menu displayed

### Adding New User
```php
php artisan tinker
$user = User::create(['name' => 'Name', 'email' => 'email@example.com', 'password' => bcrypt('password')]);
$user->assignRole('guru');  // or 'admin'
exit;
```

---

## 🚨 Troubleshooting

### Common Issues

| Error | Solution |
|-------|----------|
| Permission not found | `php artisan cache:clear && php artisan config:clear` |
| Table not found | `php artisan migrate:fresh --seed` |
| Assets not loading | `npm install && npm run build` |
| Port already in use | `php artisan serve --port=8080` |

---

## 📞 Support

- Lihat [INSTALLATION.md](./INSTALLATION.md) untuk troubleshooting lengkap
- Laravel Docs: https://laravel.com/docs
- AdminLTE: https://adminlte.io/docs

---

## 📄 License

MIT License - Gratis untuk penggunaan komersial maupun non-komersial.

---

**Last Updated:** June 4, 2026  
**Version:** 1.0.0
