# Database Schema — web-tpsr

## Stack
Laravel 11 · SQLite (local) · Spatie Permission

---

## Tabel & Relasi

### `sekolah`
| Kolom    | Tipe      | Keterangan  |
|----------|-----------|-------------|
| id       | bigint PK |             |
| nama     | varchar   | unique      |
| alamat   | varchar   |             |

Relasi: `hasMany(User)`, `hasMany(Kelas)`

---

### `users`
| Kolom      | Tipe      | Keterangan            |
|------------|-----------|-----------------------|
| id         | bigint PK |                       |
| sekolah_id | FK        | nullable → sekolah.id |
| name       | varchar   |                       |
| email      | varchar   | unique                |
| password   | varchar   | hashed                |

Role via Spatie: `admin`, `guru`
Relasi: `belongsTo(Sekolah)`

---

### `kelas`
| Kolom      | Tipe      | Keterangan             |
|------------|-----------|------------------------|
| id         | bigint PK |                        |
| sekolah_id | FK        | → sekolah.id cascade   |
| nama       | varchar   | contoh: "5-A"          |

> Unique constraint: `(sekolah_id, nama)` — satu nama kelas hanya boleh ada satu kali per sekolah.
> Tidak ada field semester/is_ganjil — kelas adalah record tunggal tanpa mempertimbangkan semester.

Relasi: `belongsTo(Sekolah)`, `hasMany(Siswa)`

---

### `siswa`
| Kolom       | Tipe      | Keterangan           |
|-------------|-----------|----------------------|
| id          | bigint PK |                      |
| kelas_id    | FK        | → kelas.id cascade   |
| nama        | varchar   |                      |
| rata_poin   | float     | default 0            |
| keterangan  | varchar   | nullable             |
| rekomendasi | varchar   | nullable             |

> Tidak ada field `gender`.
> `rata_poin` = rata-rata seluruh nilai L0–L4 di semua pertemuan.

Relasi: `belongsTo(Kelas)`, `hasMany(Penilaian)`

---

### `penilaian`
| Kolom     | Tipe                      | Keterangan                    |
|-----------|---------------------------|-------------------------------|
| id        | bigint PK                 |                               |
| siswa_id  | FK                        | → siswa.id cascade            |
| pertemuan | enum('1'…'16')            |                               |
| L0        | enum('1','2','3','4','5') | nullable — aspek/dimensi TPSR |
| L1        | enum('1','2','3','4','5') | nullable                      |
| L2        | enum('1','2','3','4','5') | nullable                      |
| L3        | enum('1','2','3','4','5') | nullable                      |
| L4        | enum('1','2','3','4','5') | nullable                      |

> Unique constraint: `(siswa_id, pertemuan)` — satu siswa satu record per pertemuan.
> Nilai L0–L4 dicast ke `integer` di model.

Relasi: `belongsTo(Siswa)`

---

## Relasi Ringkas

```
Sekolah
 ├── hasMany → User
 └── hasMany → Kelas
               └── hasMany → Siswa
                             └── hasMany → Penilaian
```

---

## Catatan
- Model `TahunAjar` masih ada sebagai deprecated stub — tidak digunakan.
- `rata_poin` pada siswa di-update manual setiap kali penilaian disimpan/dihapus.
- Kalkulasi `rata_poin`: rata-rata semua nilai L0–L4 yang tidak null di seluruh pertemuan siswa.
