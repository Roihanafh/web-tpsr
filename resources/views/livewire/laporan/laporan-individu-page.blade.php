<div class="laporan-individu-page">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @unless ($sekolah)
        <div class="alert alert-info">
            Akun ini belum memiliki relasi sekolah.
        </div>
    @endunless

    <div class="laporan-panel">

        {{-- ===== FILTER — tahun ajar dulu, kelas menyusul ===== --}}
        <div class="laporan-toolbar">
            <div class="laporan-toolbar-filters">
                <select class="form-control laporan-select" wire:model.live="tahunAjarId">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach ($tahunAjarOptions as $ta)
                        <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                    @endforeach
                </select>

                <select class="form-control laporan-select" wire:model.live="kelasId" @disabled(! $tahunAjarId)>
                    <option value="">Pilih Kelas</option>
                    @foreach ($kelasOptions as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="laporan-search-wrap">
                <span class="laporan-search-icon"><i class="fas fa-search"></i></span>
                <input
                    type="text"
                    class="form-control laporan-search"
                    placeholder="Search"
                    wire:model.live.debounce.300ms="search"
                >
            </div>
        </div>

        {{-- ===== BELUM PILIH FILTER ===== --}}
        @if (! $tahunAjarId)
            <div class="laporan-empty-state">
                <i class="fas fa-filter laporan-empty-icon"></i>
                <p class="laporan-empty-text">Pilih tahun ajaran terlebih dahulu.</p>
            </div>
        @elseif (! $kelasId)
            <div class="laporan-empty-state">
                <i class="fas fa-chalkboard laporan-empty-icon"></i>
                <p class="laporan-empty-text">Pilih kelas untuk menampilkan data laporan.</p>
            </div>
        @else

            {{-- ===== CHART AREA ===== --}}
            @if ($showChart && $chartData)
                <div class="laporan-chart-shell">
                    <div class="laporan-chart-header">
                        <div>
                            <div class="laporan-chart-title">
                                Grafik Perkembangan: <strong>{{ $chartData['nama'] }}</strong>
                            </div>
                            <div class="laporan-chart-subtitle">
                                {{ $chartData['kelas'] }} &mdash; {{ $chartData['tahun_ajar'] }}
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeChart">
                            <i class="fas fa-times mr-1"></i> Tutup
                        </button>
                    </div>

                    <div class="laporan-chart-body" wire:ignore>
                        <canvas id="siswaChart" height="110"></canvas>
                    </div>

                    <div class="laporan-chart-footer">
                        <button type="button" class="btn btn-danger laporan-download-btn" id="btnDownloadChart">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                    </div>
                </div>

                {{-- Kirim data chart ke browser via custom event --}}
                <div
                    x-data
                    x-init="
                        $nextTick(() => {
                            window.dispatchEvent(new CustomEvent('init-siswa-chart', {
                                detail: {
                                    labels: {{ Js::from($chartData['labels']) }},
                                    values: {{ Js::from($chartData['values']) }},
                                    nama:   {{ Js::from($chartData['nama']) }},
                                    slug:   {{ Js::from(\Illuminate\Support\Str::slug($chartData['nama'] ?? 'siswa')) }}
                                }
                            }));
                        })
                    "
                ></div>
            @endif

            {{-- ===== TABEL ===== --}}
            <div class="laporan-table-shell mt-4 position-relative">
                <div class="laporan-loading-layer" wire:loading.flex wire:target="tahunAjarId,kelasId,search,showDetail,closeChart">
                    <div class="laporan-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memuat data...</span>
                    </div>
                </div>

                <div class="laporan-table-head">
                    <div class="laporan-table-title">Tabel Laporan Individu</div>
                    <div class="laporan-table-subtitle">
                        Kelas {{ $kelasOptions->firstWhere('id', $kelasId)?->nama }}
                        &bull; {{ $tahunAjarOptions->firstWhere('id', $tahunAjarId)?->nama }}
                    </div>
                </div>

                @if ($siswaList->isEmpty())
                    <div class="laporan-no-data">
                        <i class="fas fa-inbox mr-2"></i> Tidak ada data siswa ditemukan.
                    </div>
                @else
                    <table class="table laporan-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Tahun Ajaran</th>
                                <th>Rata-rata</th>
                                <th class="text-center">Grafik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($siswaList as $index => $siswa)
                                <tr class="{{ $selectedSiswaId === $siswa->id ? 'laporan-row-active' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $siswa->nama }}</td>
                                    <td>{{ $siswa->kelas?->nama ?? '-' }}</td>
                                    <td>{{ $siswa->kelas?->tahunAjar?->nama ?? '-' }}</td>
                                    <td>
                                        <span class="laporan-level-badge">
                                            L{{ number_format($siswa->rata_poin, 1) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-sm {{ $selectedSiswaId === $siswa->id ? 'btn-primary' : 'btn-outline-primary' }} laporan-detail-btn"
                                            wire:click="showDetail({{ $siswa->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="showDetail({{ $siswa->id }})"
                                        >
                                            <span wire:loading.remove wire:target="showDetail({{ $siswa->id }})">
                                                <i class="fas fa-chart-line mr-1"></i> Detail
                                            </span>
                                            <span wire:loading wire:target="showDetail({{ $siswa->id }})">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        @endif
    </div>
</div>
