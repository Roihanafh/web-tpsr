<?php

namespace App\Http\Controllers;

use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $sekolah = Auth::user()?->sekolah;

        $totalKelas = 0;
        $kelasNames = '-';
        $totalSiswa = 0;
        $rataRataTPSR = 0.0;
        $persenSelesai = 0;
        $siswaBelumMengisi = 0;

        if ($sekolah) {
            // 1. Total Kelas
            $kelasList = $sekolah->kelas()->orderBy('nama')->pluck('nama')->toArray();
            $totalKelas = count($kelasList);
            if ($totalKelas > 0) {
                $kelasNames = $totalKelas > 4 
                    ? implode(', ', array_slice($kelasList, 0, 4)) . '...'
                    : implode(', ', $kelasList);
            }

            // 2. Total Siswa
            $siswaIds = Siswa::whereHas('kelas', fn($q) => $q->where('sekolah_id', $sekolah->id))->pluck('id');
            $totalSiswa = $siswaIds->count();

            // 3. Rata-rata TPSR Kelas
            if ($totalSiswa > 0) {
                $totalPenilaian = Penilaian::whereIn('siswa_id', $siswaIds)->count();
                if ($totalPenilaian > 0) {
                    $rataRataTPSR = round(Penilaian::whereIn('siswa_id', $siswaIds)->avg('level'), 1);
                }
            }

            // 4. Refleksi Mandiri / Penilaian Selesai
            if ($totalSiswa > 0) {
                $siswaBelumMengisi = Siswa::whereIn('id', $siswaIds)
                    ->whereDoesntHave('penilaian')
                    ->count();
                $siswaSudahMengisi = $totalSiswa - $siswaBelumMengisi;
                $persenSelesai = (int) round(($siswaSudahMengisi / $totalSiswa) * 100);
            }
        }

        return view('dashboard', [
            'sekolah' => $sekolah,
            'totalKelas' => $totalKelas,
            'kelasNames' => $kelasNames,
            'totalSiswa' => $totalSiswa,
            'rataRataTPSR' => $rataRataTPSR,
            'persenSelesai' => $persenSelesai,
            'siswaBelumMengisi' => $siswaBelumMengisi,
        ]);
    }
}
