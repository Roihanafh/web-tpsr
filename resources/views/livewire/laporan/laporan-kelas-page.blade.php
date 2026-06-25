<div class="laporan-individu-page">
    @unless ($sekolah)
        <div class="alert alert-info">Akun ini belum memiliki relasi sekolah.</div>
    @endunless

    <div class="laporan-panel">
        <div class="laporan-toolbar">
            <div class="laporan-search-wrap">
                <span class="laporan-search-icon"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control laporan-search" placeholder="Cari kelas..."
                    wire:model.live.debounce.300ms="search">
            </div>
        </div>

        @if ($showChart && $chartData)
            <div class="laporan-chart-shell">
                <div class="laporan-chart-header">
                    <div>
                        <div class="laporan-chart-title">
                            Grafik Perkembangan Kelas: <strong>{{ $chartData['kelas'] }}</strong>
                        </div>
                        <div class="laporan-chart-subtitle">
                            {{ $chartData['siswa_count'] }} siswa
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeChart">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                </div>

                <div class="laporan-chart-body" wire:ignore>
                    <canvas id="kelasChart" height="110"></canvas>
                </div>

                <div class="p-3 border-top bg-light">
                    <h5 class="font-weight-bold mb-3" style="font-size:1rem;">Tabel Ranking Siswa</h5>
                    <div class="table-responsive" style="border:1px solid #d1d5db;border-radius:6px;background:#fff;">
                        <table class="table laporan-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px;">Rank</th>
                                    <th>Nama Siswa</th>
                                    <th style="width:140px;text-align:center;">Rata-rata</th>
                                    <th style="width:160px;text-align:center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($siswaList as $index => $siswa)
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                        <td>{{ $siswa->nama }}</td>
                                        <td class="text-center">
                                            @if ($siswa->rata_laporan !== null)
                                                <span class="laporan-level-badge">{{ number_format($siswa->rata_laporan, 2, ',', '.') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
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
                                        <td colspan="4" class="text-center text-muted p-3">Tidak ada data siswa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 font-weight-bold text-dark">
                        Rata-rata Point Kelas :
                        <span class="badge badge-primary px-2 py-1">{{ number_format($kelasRataRata, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="laporan-chart-footer">
                    <button type="button" class="btn btn-danger laporan-download-btn" id="btnDownloadKelasChart">
                        <i class="fas fa-download mr-1"></i> Download PDF
                    </button>
                    <button type="button" class="btn btn-success laporan-download-btn ml-2"
                        wire:click="exportExcel"
                        wire:loading.attr="disabled" wire:target="exportExcel">
                        <span wire:loading.remove wire:target="exportExcel">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </span>
                        <span wire:loading wire:target="exportExcel">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Menyiapkan...
                        </span>
                    </button>
                </div>
            </div>

            <div style="position:absolute;left:-9999px;top:0;visibility:hidden;">
                @include('livewire.laporan.pdf-kelas-preview', [
                    'kelas'       => $pdfData['kelas'],
                    'pengajar'    => $pdfData['pengajar'],
                    'sekolahNama' => $pdfData['sekolahNama'],
                    'rataKelas'   => $pdfData['rataKelas'],
                    'siswaList'   => $pdfData['siswaList'],
                    'semester'    => $pdfData['semester'],
                ])
            </div>

            <div x-data x-init="
                $nextTick(() => {
                    window.dispatchEvent(new CustomEvent('init-kelas-chart', {
                        detail: {
                            labels:    {{ Js::from($chartData['labels']) }},
                            values:    {{ Js::from($chartData['values']) }},
                            kelas:     {{ Js::from($chartData['kelas']) }},
                            tahunAjar: {{ Js::from($chartData['kelas']) }},
                            slug:      {{ Js::from(\Illuminate\Support\Str::slug('laporan-kelas-' . $chartData['kelas'])) }}
                        }
                    }));
                })
            "></div>
        @endif

        <div class="laporan-table-shell mt-4 position-relative">
            <div class="laporan-loading-layer" wire:loading.flex wire:target="search,showDetail,closeChart">
                <div class="laporan-loading-box">
                    <i class="fas fa-spinner fa-spin"></i><span>Memuat data...</span>
                </div>
            </div>

            <div class="laporan-table-head">
                <div class="laporan-table-title">Tabel Laporan Kelas</div>
                <div class="laporan-table-subtitle">{{ $semesterLabel }}</div>
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
                                <th class="text-center">Grafik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kelasList as $index => $kelas)
                                <tr class="{{ $selectedKelasId === $kelas->id ? 'laporan-row-active' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $kelas->nama }}</td>
                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-sm {{ $selectedKelasId === $kelas->id ? 'btn-primary' : 'btn-outline-primary' }} laporan-detail-btn"
                                            wire:click="showDetail({{ $kelas->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="showDetail({{ $kelas->id }})">
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
    </div>
</div>
