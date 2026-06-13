<div class="container-fluid px-0">
    
    {{-- ===== BANNER CARD ===== --}}
    <div class="tpsr-banner">
        <div class="tpsr-banner-content">
            <span class="tpsr-banner-badge">Dashboard Utama</span>
            <h1 class="tpsr-banner-title">Evaluasi Sikap & Tanggung Jawab (TPSR)</h1>
            <p class="tpsr-banner-subtitle">
                Pantau perkembangan karakter tanggung jawab sosial dan personal siswa Anda secara efisien.
            </p>
            <div class="tpsr-banner-actions">
                <a href="{{ route('assessment.index') }}" class="btn tpsr-banner-btn-primary">
                    <i class="fas fa-running mr-1"></i> Mulai Penilaian Lapangan
                </a>
                <a href="https://docs.google.com/document/d/1WyBACqCJiBuYa45lEYq6f6vu3rfsGccA8OXm8n9NCKI/edit?usp=sharing" class="btn tpsr-banner-btn-secondary">
                    <i class="fas fa-book-open mr-1"></i> Buku Panduan
                </a>
            </div>
        </div>
        <div class="tpsr-banner-graphic d-none d-md-flex">
            <!-- Dynamic sleek running person watermark graphic -->
            <i class="fas fa-running tpsr-banner-icon"></i>
        </div>
    </div>

    {{-- ===== STAT CARDS GRID ===== --}}
    <div class="tpsr-stat-grid">
        
        {{-- Card 1: Total Kelas --}}
        <div class="tpsr-stat-card">
            <div class="tpsr-stat-content">
                <span class="tpsr-stat-title">Total Kelas Diampu</span>
                <span class="tpsr-stat-value">{{ $totalKelas }} Kelas</span>
            </div>
            <div class="tpsr-stat-icon-wrapper bg-blue-light">
                <i class="fas fa-school"></i>
            </div>
        </div>

        {{-- Card 2: Siswa Terdaftar --}}
        <div class="tpsr-stat-card">
            <div class="tpsr-stat-content">
                <span class="tpsr-stat-title">Siswa Terdaftar</span>
                <span class="tpsr-stat-value">{{ $totalSiswa }} Siswa</span>
            </div>
            <div class="tpsr-stat-icon-wrapper bg-green-light">
                <i class="fas fa-user-friends"></i>
            </div>
        </div>

        {{-- Card 3: Rata-rata TPSR Kelas --}}
        <div class="tpsr-stat-card">
            <div class="tpsr-stat-content">
                <span class="tpsr-stat-title">Rata-rata TPSR Kelas</span>
                <span class="tpsr-stat-value">Level {{ number_format($rataRataTPSR, 1, '.', ',') }}</span>
            </div>
            <div class="tpsr-stat-icon-wrapper bg-orange-light">
                <i class="fas fa-tachometer-alt"></i>
            </div>
        </div>

        {{-- Card 4: Refleksi Mandiri --}}
        <div class="tpsr-stat-card">
            <div class="tpsr-stat-content">
                <span class="tpsr-stat-title">Refleksi Mandiri</span>
                <span class="tpsr-stat-value">{{ $persenSelesai }}% Selesai</span>
                <span class="tpsr-stat-footer{{ $siswaBelumMengisi > 0 ? '-warning' : '-success' }}">
                    @if ($siswaBelumMengisi > 0)
                        <i class="fas fa-exclamation-triangle"></i> {{ $siswaBelumMengisi }} Siswa Belum Dinilai
                    @else
                        <i class="fas fa-check-circle"></i> Semua Siswa Sudah Dinilai
                    @endif
                </span>
            </div>
            <div class="tpsr-stat-icon-wrapper bg-purple-light">
                <i class="fas fa-user-check"></i>
            </div>
        </div>

    </div>

</div>
