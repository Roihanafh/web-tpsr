<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'sekolah_id',
        'nama',
        'is_ganjil',
    ];

    protected function casts(): array
    {
        return [
            'is_ganjil' => 'boolean',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }
}
