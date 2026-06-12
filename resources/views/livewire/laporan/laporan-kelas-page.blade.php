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

        {{-- ===== FILTER — Hanya Tahun Ajaran ===== --}}
        @php
            $uniqueYears = $tahunAjarOptions->map(function ($ta) {
                return trim(str_ireplace(['ganjil', 'genap'], '', $ta->nama));
            })->unique()->filter()->values()->toArray();
        @endphp
        <div class="laporan-toolbar">
            <div class="laporan-toolbar-filters">
                <select class="form-control laporan-select" wire:model.live="tahunAjarId" style="width: 250px !important;">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach ($uniqueYears as $year)
                        {{-- Opsi tahun ajaran keseluruhan (Semua Semester) --}}
                        <option value="year:{{ $year }}">{{ $year }} (Semua Semester)</option>
                        {{-- Opsi spesifik ganjil/genap --}}
                        @foreach ($tahunAjarOptions->filter(fn($ta) => str_starts_with($ta->nama, $year)) as $ta)
                            <option value="{{ $ta->id }}">&nbsp;&nbsp;&bull;&nbsp;{{ $ta->nama }}</option>
                        @endforeach
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
                    @disabled(! $tahunAjarId)
                >
            </div>
        </div>

        {{-- ===== BELUM PILIH FILTER ===== --}}
        @if (! $tahunAjarId)
            <div class="laporan-empty-state">
                <i class="fas fa-filter laporan-empty-icon"></i>
                <p class="laporan-empty-text">Pilih tahun ajaran terlebih dahulu.</p>
            </div>
        @else

            {{-- ===== DETAIL VIEW & CHART AREA ===== --}}
            @if ($showChart && $chartData)
                <div class="laporan-chart-shell">
                    <div class="laporan-chart-header">
                        <div>
                            <div class="laporan-chart-title">
                                Grafik Perkembangan Kelas: <strong>{{ $chartData['kelas'] }}</strong>
                            </div>
                            <div class="laporan-chart-subtitle">
                                Tahun Ajar: {{ $chartData['tahun_ajar'] }} &bull; {{ $chartData['siswa_count'] }} siswa
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeChart">
                            <i class="fas fa-times mr-1"></i> Tutup
                        </button>
                    </div>

                    <div class="laporan-chart-body" wire:ignore>
                        <canvas id="kelasChart" height="110"></canvas>
                    </div>

                    {{-- Tabel Ranking Siswa di Screen (UI) --}}
                    <div class="p-3 border-top bg-light">
                        <h5 class="font-weight-bold mb-3" style="font-size: 1rem; color: #111827;">Tabel Ranking Siswa</h5>
                        <div class="table-responsive" style="border: 1px solid #d1d5db; border-radius: 6px; background: #fff;">
                            <table class="table laporan-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Rank</th>
                                        <th>Nama Siswa</th>
                                        <th style="width: 140px; text-align: center;">Rata-rata Point</th>
                                        <th style="width: 160px; text-align: center;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($siswaList as $index => $siswa)
                                        <tr>
                                            <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                            <td>{{ $siswa->nama }}</td>
                                            <td class="text-center">
                                                @if ($siswa->rata_laporan !== null)
                                                    <span class="laporan-level-badge">
                                                        {{ number_format($siswa->rata_laporan, 2, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted" style="font-size:.82rem;">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($siswa->rata_laporan !== null)
                                                    <span class="badge {{ $siswa->rata_laporan >= 3.0 ? 'badge-success' : ($siswa->rata_laporan >= 2.0 ? 'badge-info' : 'badge-warning') }}">
                                                        {{ $siswa->status_laporan }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted p-3">Tidak ada data siswa di kelas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 font-weight-bold text-dark" style="font-size: .95rem;">
                            Rata-rata Point Kelas : <span class="badge badge-primary px-2 py-1" style="font-size: .95rem;">{{ number_format($kelasRataRata, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="laporan-chart-footer">
                        <button type="button" class="btn btn-danger laporan-download-btn" id="btnDownloadKelasChart">
                            <i class="fas fa-download mr-1"></i> Download PDF
                        </button>
                    </div>
                </div>

                {{-- Hidden PDF template --}}
                <div style="position:absolute; left:-9999px; top:0; visibility:hidden;">
                    @include('livewire.laporan.pdf-kelas-preview', [
                        'kelas'       => $pdfData['kelas'],
                        'pengajar'    => $pdfData['pengajar'],
                        'sekolahNama' => $pdfData['sekolahNama'],
                        'rataKelas'   => $pdfData['rataKelas'],
                        'siswaList'   => $pdfData['siswaList'],
                    ])
                </div>

                {{-- Kirim data chart ke browser via custom event --}}
                <div
                    x-data
                    x-init="
                        $nextTick(() => {
                            window.dispatchEvent(new CustomEvent('init-kelas-chart', {
                                detail: {
                                    labels: {{ Js::from($chartData['labels']) }},
                                    values: {{ Js::from($chartData['values']) }},
                                    kelas:  {{ Js::from($chartData['kelas']) }},
                                    slug:   {{ Js::from(\Illuminate\Support\Str::slug('laporan-kelas-' . $chartData['kelas'] ?? 'kelas')) }}
                                }
                            }));
                        })
                    "
                ></div>
            @endif

            {{-- ===== TABEL KELAS ===== --}}
            <div class="laporan-table-shell mt-4 position-relative">
                <div class="laporan-loading-layer" wire:loading.flex wire:target="tahunAjarId,search,showDetail,closeChart">
                    <div class="laporan-loading-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Memuat data...</span>
                    </div>
                </div>

                <div class="laporan-table-head">
                    <div class="laporan-table-title">Tabel Laporan Kelas</div>
                    <div class="laporan-table-subtitle">
                        Tahun Ajaran {{ $tahunAjarLabel }}
                    </div>
                </div>

                @if ($kelasList->isEmpty())
                    <div class="laporan-no-data">
                        <i class="fas fa-inbox mr-2"></i> Tidak ada data kelas ditemukan.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table laporan-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th class="text-center">Grafik</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kelasList as $index => $kelas)
                                    <tr class="{{ $selectedKelasId === $kelas->id ? 'laporan-row-active' : '' }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $kelas->nama }}</td>
                                        <td>{{ $kelas->tahunAjar?->nama ?? '-' }}</td>
                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm {{ $selectedKelasId === $kelas->id ? 'btn-primary' : 'btn-outline-primary' }} laporan-detail-btn"
                                                wire:click="showDetail({{ $kelas->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="showDetail({{ $kelas->id }})"
                                            >
                                                <span wire:loading.remove wire:target="showDetail({{ $kelas->id }})">
                                                    <i class="fas fa-chart-line mr-1"></i> Detail
                                                </span>
                                                <span wire:loading wire:target="showDetail({{ $kelas->id }})">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        @endif
    </div>
</div>

