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
        $user = Auth::user();

        if ($user && $user->hasRole('admin')) {
            $totalSekolah = \App\Models\Sekolah::count();
            $totalGuru = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'guru'))->count();
            $totalAdmin = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->count();
            $totalTahunAjar = \App\Models\TahunAjar::count();
            $totalKelas = \App\Models\Kelas::count();
            $totalSiswa = \App\Models\Siswa::count();
            $totalPenilaian = \App\Models\Penilaian::count();

            // Fetch list of schools and users for the tables
            $recentSekolah = \App\Models\Sekolah::orderBy('id', 'desc')->take(5)->get();
            $recentGuru = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'guru'))
                ->with('sekolah')
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();
            $recentTahunAjar = \App\Models\TahunAjar::getSorted()->take(5);

            return view('dashboard', [
                'isAdmin' => true,
                'totalSekolah' => $totalSekolah,
                'totalGuru' => $totalGuru,
                'totalAdmin' => $totalAdmin,
                'totalTahunAjar' => $totalTahunAjar,
                'totalKelas' => $totalKelas,
                'totalSiswa' => $totalSiswa,
                'totalPenilaian' => $totalPenilaian,
                'recentSekolah' => $recentSekolah,
                'recentGuru' => $recentGuru,
                'recentTahunAjar' => $recentTahunAjar,
            ]);
        }

        // Guru Dashboard Data (same as before)
        $sekolah = $user?->sekolah;

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
            'isAdmin' => false,
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
