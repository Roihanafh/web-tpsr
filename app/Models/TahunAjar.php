<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Model TahunAjar sudah tidak digunakan.
 * Kelas tidak lagi memiliki relasi tahun ajar.
 */
class TahunAjar extends Model
{
    protected $table = 'tahun_ajar';

    protected $fillable = ['nama'];
}
