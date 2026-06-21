# Database Schema — web-tpsr

## Stack
Laravel 11 · SQLite (local) · Spatie Permission

---

## Tabel & Relasi

### `sekolah`
| Kolom    | Tipe         | Keterangan         |
|----------|--------------|--------------------|
| id       | bigint PK    |                    |
| nama     | varchar      | unique             |
| alamat   | varchar      |                    |

Relasi: `hasMany(User)`, `hasMany(Kelas)`

---

### `users`
| Kolom      | Tipe      | Keterangan              |
|------------|-----------|-------------------------|
| id         | bigint PK |                         |
| sekolah_id | FK        | nullable → sekolah.id   |
| name       | varchar   |                         |
| email      | varchar   | unique                  |
| password   | varchar   | hashed                  |

Role via Spatie: `admin`, `guru`  
Relasi: `belongsTo(Sekolah)`

---

### `kelas`
| Kolom      | Tipe      | Keterangan           |
|------------|-----------|----------------------|
| id         | bigint PK |                      |
| sekolah_id | FK        | → sekolah.id cascade |
| nama       | varchar   | contoh: "5-A"        |

> Tidak ada tahun ajar / semester. Satu kelas = satu record permanen milik sekolah.

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
| Kolom    | Tipe                       | Keterangan                        |
|----------|----------------------------|-----------------------------------|
| id       | bigint PK                  |                                   |
| siswa_id | FK                         | → siswa.id cascade                |
| pertemuan| enum('1'…'16')             |                                   |
| L0       | enum('1','2','3','4','5')  | nullable — aspek/dimensi TPSR     |
| L1       | enum('1','2','3','4','5')  | nullable                          |
| L2       | enum('1','2','3','4','5')  | nullable                          |
| L3       | enum('1','2','3','4','5')  | nullable                          |
| L4       | enum('1','2','3','4','5')  | nullable                          |

> Tidak ada field `level` (sudah dihapus).  
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
- Migration `2026_06_07_000002_create_tahun_ajar_table` adalah stub kosong (tabel tidak dibuat).
- `rata_poin` pada siswa di-update manual setiap kali penilaian disimpan.
