<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjar extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajar';

    protected $fillable = [
        'nama',
    ];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public static function getSorted()
    {
        return self::all()->sort(function ($a, $b) {
            $yearA = substr($a->nama, 0, 9);
            $yearB = substr($b->nama, 0, 9);

            if ($yearA !== $yearB) {
                return strcmp($yearB, $yearA);
            }

            $semA = str_contains(strtolower($a->nama), 'ganjil') ? 0 : 1;
            $semB = str_contains(strtolower($b->nama), 'ganjil') ? 0 : 1;

            return $semA <=> $semB;
        })->values();
    }
}
