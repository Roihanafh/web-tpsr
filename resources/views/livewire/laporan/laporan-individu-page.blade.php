<div class="laporan-individu-page">
    @unless ($sekolah)
        <div class="alert alert-info">Akun ini belum memiliki relasi sekolah.</div>
    @endunless

    <div class="laporan-panel">
        <div class="laporan-toolbar">
            <div class="laporan-toolbar-filters">
                <select class="form-control laporan-select" wire:model.live="kelasId">
                    <option value="">Pilih Kelas</option>
                    <option value="all">Semua Kelas</option>
                    @foreach ($kelasOptions as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="laporan-search-wrap">
                <span class="laporan-search-icon"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control laporan-search" placeholder="Search"
                    wire:model.live.debounce.300ms="search" @disabled(!$kelasId)>
            </div>
        </div>

        @if (!$kelasId)
            <div class="laporan-empty-state">
                <i class="fas fa-chalkboard laporan-empty-icon"></i>
                <p class="laporan-empty-text">Pilih kelas untuk menampilkan data laporan.</p>
            </div>
        @else

            @if ($showChart && $chartData)
                <div class="laporan-chart-shell">
                    <div class="laporan-chart-header">
                        <div>
                            <div class="laporan-chart-title">
                                Grafik Perkembangan: <strong>{{ $chartData['nama'] }}</strong>
                            </div>
                            <div class="laporan-chart-subtitle">
                                Kelas {{ $chartData['kelas'] }}
                                &bull; {{ $chartData['pertemuan_dinilai'] }} pertemuan dinilai
                                @if ($chartData['rata_laporan'] !== null)
                                    &bull; Rata-rata: <strong>{{ number_format($chartData['rata_laporan'], 2) }}</strong>
                                @endif
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

                <div style="position:absolute;left:-9999px;top:0;visibility:hidden;">
                    @include('livewire.laporan.pdf-preview', [
                        'siswa'       => $pdfData['siswa'],
                        'pengajar'    => $pdfData['pengajar'],
                        'sekolahNama' => $pdfData['sekolahNama'],
                        'rataLaporan' => $pdfData['rataLaporan'],
                        'status'      => $pdfData['status'],
                        'semester'    => $pdfData['semester'],
                    ])
                </div>

                <div x-data x-init="
                    $nextTick(() => {
                        window.dispatchEvent(new CustomEvent('init-siswa-chart', {
                            detail: {
                                labels:    {{ Js::from($chartData['labels']) }},
                                values:    {{ Js::from($chartData['values']) }},
                                nama:      {{ Js::from($chartData['nama']) }},
                                kelas:     {{ Js::from($chartData['kelas']) }},
                                tahunAjar: {{ Js::from($chartData['kelas']) }},
                                slug:      {{ Js::from(\Illuminate\Support\Str::slug($chartData['nama'] ?? 'siswa')) }}
                            }
                        }));
                    })
                "></div>
            @endif

            <div class="laporan-table-shell mt-4 position-relative">
                <div class="laporan-loading-layer" wire:loading.flex wire:target="kelasId,search,showDetail,closeChart">
                    <div class="laporan-loading-box">
                        <i class="fas fa-spinner fa-spin"></i><span>Memuat data...</span>
                    </div>
                </div>

                <div class="laporan-table-head">
                    <div class="laporan-table-title">Tabel Laporan Individu</div>
                    <div class="laporan-table-subtitle">
                        Kelas {{ $kelasOptions->firstWhere('id', $kelasId)?->nama ?? 'Semua' }}
                    </div>
                </div>

                @if ($siswaList->isEmpty())
                    <div class="laporan-no-data">
                        <i class="fas fa-inbox mr-2"></i> Tidak ada data siswa ditemukan.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table laporan-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
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
                                        <td>
                                            @if ($siswa->rata_laporan !== null)
                                                <span class="laporan-level-badge">{{ number_format($siswa->rata_laporan, 2) }}</span>
                                                <span class="laporan-pertemuan-info">/ {{ $siswa->pertemuan_dinilai }} pertemuan</span>
                                            @else
                                                <span class="text-muted" style="font-size:.82rem;">Belum dinilai</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-sm {{ $selectedSiswaId === $siswa->id ? 'btn-primary' : 'btn-outline-primary' }} laporan-detail-btn"
                                                wire:click="showDetail({{ $siswa->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="showDetail({{ $siswa->id }})">
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
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
