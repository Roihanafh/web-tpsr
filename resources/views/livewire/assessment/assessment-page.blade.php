<div class="assessment-page">
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

    <div class="assessment-panel">
        <div class="assessment-toolbar">
            <div class="assessment-toolbar-filters">
                <select class="form-control assessment-select" wire:model.live="kelasId">
                    <option value="">Pilih Kelas</option>
                    @foreach ($kelasOptions as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                    @endforeach
                </select>

                <select class="form-control assessment-select" wire:model.live="pertemuan" @disabled(!$kelasId)>
                    <option value="">Pilih Pertemuan</option>
                    @for ($i = 1; $i <= 16; $i++)
                        <option value="{{ $i }}">Pertemuan {{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="assessment-actions">
                <button type="button" class="btn btn-success assessment-btn" wire:click="toggleImportForm" wire:loading.attr="disabled">
                    <i class="fas fa-file-import mr-1"></i>Import Excel
                </button>
            </div>
        </div>

        @if ($showImportForm)
            <div class="assessment-form-grid position-relative mt-3" style="background: #f9fafb; padding: 1rem; border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 1.5rem;">
                <div class="assessment-loading-layer" wire:loading.flex wire:target="import,downloadTemplate">
                    <div class="assessment-loading-box">
                        <i class="fas fa-spinner fa-spin"></i><span>Memproses...</span>
                    </div>
                </div>

                <form wire:submit="import" class="assessment-import">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0 font-weight-bold">Import Excel Penilaian</label>
                        <button type="button" class="btn btn-link p-0 text-primary font-weight-bold" wire:click="downloadTemplate">
                            Download template
                        </button>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-2">
                            <div class="input-group">
                                <input type="file"
                                    class="form-control @error('fileImport') is-invalid @enderror"
                                    wire:model="fileImport" accept=".xlsx,.xls,.csv"
                                    wire:loading.attr="disabled" wire:target="fileImport,import">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success font-weight-bold"
                                        wire:loading.attr="disabled" wire:target="fileImport,import">
                                        <span wire:loading.remove wire:target="fileImport,import">
                                            <i class="fas fa-file-import mr-1"></i>Import Excel
                                        </span>
                                        <span wire:loading wire:target="fileImport,import">
                                            <i class="fas fa-spinner fa-spin mr-1"></i>Mengimport
                                        </span>
                                    </button>
                                </div>
                                @error('fileImport') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        @if ($importFailures !== [])
            <div class="alert alert-warning mt-3 mb-3">
                <strong>Data gagal masuk database:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($importFailures as $failure)
                        <li>Baris {{ $failure['line'] }}: {{ $failure['message'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($kelasId && $pertemuan)
            @if ($students->isNotEmpty())
                <div class="assessment-table-shell mt-4 position-relative">
                    <div class="assessment-loading-layer" wire:loading.flex wire:target="kelasId,pertemuan,save">
                        <div class="assessment-loading-box">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Memproses...</span>
                        </div>
                    </div>

                    <div class="assessment-table-head d-flex justify-content-between align-items-center">
                        <div>
                            <div class="assessment-table-title">Lembar Penilaian TPSR</div>
                            <div class="assessment-table-subtitle">
                                Pertemuan {{ $pertemuan }} &bull;
                                Kelas {{ $kelasOptions->firstWhere('id', $kelasId)?->nama }}
                            </div>
                        </div>
                        <div>
                            @if ($isAssessed)
                                <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 0.82rem; border-radius: 4px;">
                                    <i class="fas fa-check-circle mr-1"></i>Sudah Dinilai
                                </span>
                            @else
                                <span class="badge badge-secondary px-3 py-2 font-weight-bold" style="font-size: 0.82rem; border-radius: 4px; background-color: #6b7280; color: #ffffff;">
                                    <i class="fas fa-history mr-1"></i>Belum Dinilai
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover assessment-table mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th class="text-center" style="width: 10%;">L0</th>
                                    <th class="text-center" style="width: 10%;">L1</th>
                                    <th class="text-center" style="width: 10%;">L2</th>
                                    <th class="text-center" style="width: 10%;">L3</th>
                                    <th class="text-center" style="width: 10%;">L4</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $siswa)
                                    <tr wire:key="row-{{ $siswa->id }}-{{ $pertemuan }}">
                                        <td class="align-middle font-weight-bold">{{ $siswa->nama }}</td>
                                        @foreach (['L0','L1','L2','L3','L4'] as $lvl)
                                            <td class="text-center align-middle" wire:key="{{ $siswa->id }}-{{ $lvl }}-{{ $pertemuan }}-{{ $renderKey }}">
                                                <select class="form-control form-control-sm"
                                                    wire:model="ratings.{{ $siswa->id }}.{{ $lvl }}"
                                                    style="min-width: 60px;">
                                                    <option value="">-</option>
                                                    @for ($v = 1; $v <= 5; $v++)
                                                        <option value="{{ $v }}">{{ $v }}</option>
                                                    @endfor
                                                </select>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="assessment-table-foot p-3 bg-light d-flex flex-wrap justify-content-end" style="gap: 0.5rem;">
                        <button type="button" class="btn btn-outline-danger px-4 py-2 font-weight-bold"
                            wire:click="kosongkanPenilaian" wire:loading.attr="disabled" wire:target="kosongkanPenilaian">
                            <span wire:loading.remove wire:target="kosongkanPenilaian">
                                <i class="fas fa-eraser mr-2"></i>Kosongkan Penilaian
                            </span>
                            <span wire:loading wire:target="kosongkanPenilaian">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Mengosongkan...
                            </span>
                        </button>

                        <button type="button" class="btn btn-primary px-4 py-2 font-weight-bold"
                            wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save mr-2"></i>Simpan Penilaian
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...
                            </span>
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle mr-2"></i>Tidak ada data siswa untuk kelas ini.
                </div>
            @endif
        @endif
    </div>
</div>
