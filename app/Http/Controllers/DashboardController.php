<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user && $user->hasRole('admin')) {
            return view('dashboard', [
                'isAdmin'      => true,
                'totalSekolah' => Sekolah::count(),
                'totalGuru'    => User::whereHas('roles', fn ($q) => $q->where('name', 'guru'))->count(),
                'totalAdmin'   => User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count(),
                'totalKelas'   => Kelas::count(),
                'totalSiswa'   => Siswa::count(),
                'totalPenilaian' => Penilaian::count(),
                'recentSekolah' => Sekolah::orderBy('id', 'desc')->take(5)->get(),
                'recentGuru'   => User::whereHas('roles', fn ($q) => $q->where('name', 'guru'))
                    ->with('sekolah')
                    ->orderBy('id', 'desc')
                    ->take(5)
                    ->get(),
            ]);
        }

        // Guru Dashboard
        $sekolah = $user?->sekolah;

        $totalKelas      = 0;
        $kelasNames      = '-';
        $totalSiswa      = 0;
        $rataRataTPSR    = 0.0;
        $persenSelesai   = 0;
        $siswaBelumMengisi = 0;

        if ($sekolah) {
            $kelasList  = $sekolah->kelas()->orderBy('nama')->pluck('nama')->toArray();
            $totalKelas = count($kelasList);
            if ($totalKelas > 0) {
                $kelasNames = $totalKelas > 4
                    ? implode(', ', array_slice($kelasList, 0, 4)) . '...'
                    : implode(', ', $kelasList);
            }

            $siswaIds   = Siswa::whereHas('kelas', fn ($q) => $q->where('sekolah_id', $sekolah->id))->pluck('id');
            $totalSiswa = $siswaIds->count();

            if ($totalSiswa > 0) {
                $allPenilaian = Penilaian::whereIn('siswa_id', $siswaIds);

                if ($allPenilaian->count() > 0) {
                    // Rata-rata dari semua nilai L0-L4
                    $levelSums = ['L0', 'L1', 'L2', 'L3', 'L4'];
                    $total = 0;
                    $count = 0;
                    foreach (Penilaian::whereIn('siswa_id', $siswaIds)->get() as $p) {
                        foreach ($levelSums as $l) {
                            if ($p->{$l} !== null) {
                                $total += (int) $p->{$l};
                                $count++;
                            }
                        }
                    }
                    $rataRataTPSR = $count > 0 ? round($total / $count, 1) : 0;
                }

                $totalExpected = $totalSiswa * 16;
                $totalActual   = Penilaian::whereIn('siswa_id', $siswaIds)->count();
                $persenSelesai = $totalExpected > 0
                    ? (int) round(($totalActual / $totalExpected) * 100)
                    : 0;

                $siswaBelumMengisi = Siswa::whereIn('id', $siswaIds)
                    ->withCount('penilaian')
                    ->get()
                    ->filter(fn ($s) => $s->penilaian_count < 16)
                    ->count();
            }
        }

        return view('dashboard', [
            'isAdmin'           => false,
            'sekolah'           => $sekolah,
            'totalKelas'        => $totalKelas,
            'kelasNames'        => $kelasNames,
            'totalSiswa'        => $totalSiswa,
            'rataRataTPSR'      => $rataRataTPSR,
            'persenSelesai'     => $persenSelesai,
            'siswaBelumMengisi' => $siswaBelumMengisi,
        ]);
    }
}
