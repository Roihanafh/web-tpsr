<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'kelas_id',
        'nama',
        'gender',
        'rata_poin',
    ];

    protected function casts(): array
    {
        return [
            'rata_poin' => 'float',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    public function tahunAjar(): HasOneThrough
    {
        return $this->hasOneThrough(
            TahunAjar::class, // Model tujuan akhir
            Kelas::class,     // Model perantara
            'id',             // Foreign key di tabel Kelas (id kelas)
            'id',             // Foreign key di tabel TahunAjar (id tahun ajar)
            'kelas_id',       // Local key di tabel Siswa
            'tahun_ajar_id'   // Local key di tabel Kelas
        );
    }
}
