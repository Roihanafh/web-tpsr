<div class="laporan-individu-page">
    @unless ($sekolah)
        <div class="alert alert-info">
            Akun ini belum memiliki relasi sekolah.
        </div>
    @endunless

    <div class="laporan-panel">

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

        <div class="laporan-empty-state">
            <i class="fas fa-chart-bar laporan-empty-icon"></i>
            <p class="laporan-empty-text">Fitur laporan kelas akan segera hadir.</p>
        </div>

    </div>
</div>
