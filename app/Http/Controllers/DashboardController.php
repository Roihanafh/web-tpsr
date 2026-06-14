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

            // 2. Total Siswa (Distinct physical students by name and gender in the school)
            $siswaIds = Siswa::whereHas('kelas', fn($q) => $q->where('sekolah_id', $sekolah->id))->pluck('id');
            $totalSiswa = Siswa::whereIn('id', $siswaIds)
                ->select('nama', 'gender')
                ->groupBy('nama', 'gender')
                ->get()
                ->count();

            // 3. Rata-rata TPSR Kelas
            $totalSiswaRecords = $siswaIds->count();
            if ($totalSiswaRecords > 0) {
                $totalPenilaian = Penilaian::whereIn('siswa_id', $siswaIds)->count();
                if ($totalPenilaian > 0) {
                    $rataRataTPSR = round(Penilaian::whereIn('siswa_id', $siswaIds)->avg('level'), 1);
                }
            }

            // 4. Refleksi Mandiri / Penilaian Selesai (dihitung berdasarkan keseluruhan pertemuan untuk seluruh semester)
            if ($totalSiswaRecords > 0) {
                // Total expected evaluations across all semesters (16 meetings per student record)
                $totalExpectedEvaluations = $totalSiswaRecords * 16;
                $totalActualEvaluations = Penilaian::whereIn('siswa_id', $siswaIds)->count();
                $persenSelesai = $totalExpectedEvaluations > 0 
                    ? (int) round(($totalActualEvaluations / $totalExpectedEvaluations) * 100) 
                    : 0;

                // Distinct physical students who have not completed all 16 evaluations in all semesters they are registered in
                $siswaBelumMengisi = Siswa::whereIn('id', $siswaIds)
                    ->withCount('penilaian')
                    ->get()
                    ->filter(fn($s) => $s->penilaian_count < 16)
                    ->groupBy(fn($s) => $s->nama . '|' . $s->gender)
                    ->count();
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
