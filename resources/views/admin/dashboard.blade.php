<div class="container-fluid px-0">

    {{-- ===== BANNER CARD ===== --}}
    <div class="tpsr-banner">
        <div class="tpsr-banner-content">
            <span class="tpsr-banner-badge">Panel Administrator</span>
            <h1 class="tpsr-banner-title">Manajemen Sistem Evaluasi TPSR</h1>
            <p class="tpsr-banner-subtitle">
                Kelola data sekolah dan manajemen akun pengguna (guru & admin) secara terpusat.
            </p>
            <div class="tpsr-banner-actions">
                <a href="{{ route('admin.users') }}" class="btn tpsr-banner-btn-primary">
                    <i class="fas fa-users-cog mr-1"></i> Kelola Pengguna
                </a>
                <a href="{{ route('admin.sekolah') }}" class="btn tpsr-banner-btn-secondary">
                    <i class="fas fa-school mr-1"></i> Kelola Sekolah
                </a>
                <a href="#" data-toggle="modal" data-target="#panduanModal" class="btn tpsr-banner-btn-secondary">
                    <i class="fas fa-book-open mr-1"></i> Buku Panduan
                </a>
            </div>
        </div>
        <div class="tpsr-banner-graphic d-none d-md-flex">
            <i class="fas fa-cogs tpsr-banner-icon"></i>
        </div>
    </div>

    {{-- ===== STAT CARDS GRID ===== --}}
    <div class="tpsr-stat-grid">

        <div class="tpsr-stat-card">
            <div class="tpsr-stat-content">
                <span class="tpsr-stat-title">Total Sekolah</span>
                <span class="tpsr-stat-value">{{ $totalSekolah }} Sekolah</span>
                <span class="tpsr-stat-footer text-muted" style="font-size: .8rem;">Telah terdaftar dalam sistem</span>
            </div>
            <div class="tpsr-stat-icon-wrapper bg-blue-light">
                <i class="fas fa-school"></i>
            </div>
        </div>

        <div class="tpsr-stat-card">
            <div class="tpsr-stat-content">
                <span class="tpsr-stat-title">Total Guru Pengajar</span>
                <span class="tpsr-stat-value">{{ $totalGuru }} Guru</span>
                <span class="tpsr-stat-footer text-muted" style="font-size: .8rem;">{{ $totalAdmin }} Akun Admin</span>
            </div>
            <div class="tpsr-stat-icon-wrapper bg-green-light">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <div class="tpsr-stat-card">
            <div class="tpsr-stat-content">
                <span class="tpsr-stat-title">Total Kelas</span>
                <span class="tpsr-stat-value">{{ $totalKelas }} Kelas</span>
                <span class="tpsr-stat-footer text-muted" style="font-size: .8rem;">Dari seluruh sekolah</span>
            </div>
            <div class="tpsr-stat-icon-wrapper bg-orange-light">
                <i class="fas fa-chalkboard"></i>
            </div>
        </div>

        <div class="tpsr-stat-card">
            <div class="tpsr-stat-content">
                <span class="tpsr-stat-title">Total Siswa</span>
                <span class="tpsr-stat-value">{{ $totalSiswa }} Siswa</span>
                <span class="tpsr-stat-footer text-muted" style="font-size: .8rem;">Telah dinilai: {{ $totalPenilaian }}x</span>
            </div>
            <div class="tpsr-stat-icon-wrapper bg-purple-light">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>

    </div>

    {{-- ===== DETAIL TABLES ===== --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 1rem;">
                            <i class="fas fa-school text-primary mr-2"></i> Pendaftaran Sekolah Terbaru
                        </h5>
                        <a href="{{ route('admin.sekolah') }}" class="btn btn-sm btn-light text-primary font-weight-bold">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 0.9rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-4">Nama Sekolah</th>
                                    <th class="border-0 text-center">Kelas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentSekolah as $sch)
                                    <tr>
                                        <td class="px-4 font-weight-bold text-dark">{{ $sch->nama }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-pill badge-primary px-2 py-1">{{ $sch->kelas()->count() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">Belum ada sekolah terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 1rem;">
                            <i class="fas fa-users text-success mr-2"></i> Pendaftaran Guru Terbaru
                        </h5>
                        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-light text-success font-weight-bold">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 0.9rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-4">Nama</th>
                                    <th class="border-0">Email</th>
                                    <th class="border-0">Sekolah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentGuru as $teacher)
                                    <tr>
                                        <td class="px-4 font-weight-bold text-dark">{{ $teacher->name }}</td>
                                        <td class="text-muted">{{ $teacher->email }}</td>
                                        <td>
                                            @if ($teacher->sekolah)
                                                <span class="badge badge-pill badge-success px-2 py-1">{{ $teacher->sekolah->nama }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Belum ada guru terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
