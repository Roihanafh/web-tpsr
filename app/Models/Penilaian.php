<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'siswa_id',
        'pertemuan',
        'L0',
        'L1',
        'L2',
        'L3',
        'L4',
    ];

    protected function casts(): array
    {
        return [
            'L0' => 'integer',
            'L1' => 'integer',
            'L2' => 'integer',
            'L3' => 'integer',
            'L4' => 'integer',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
