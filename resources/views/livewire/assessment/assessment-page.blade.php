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
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="assessment-panel">
        <div class="assessment-toolbar">
            <div class="assessment-toolbar-filters">
                <select id="tahunAjarId" class="form-control assessment-select" wire:model.live="tahunAjarId">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach ($tahunAjarOptions as $ta)
                        <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                    @endforeach
                </select>

                <select id="kelasId" class="form-control assessment-select" wire:model.live="kelasId" @disabled(!$tahunAjarId)>
                    <option value="">Pilih Kelas</option>
                    @foreach ($kelasOptions as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                    @endforeach
                </select>

                <select id="pertemuan" class="form-control assessment-select" wire:model.live="pertemuan" @disabled(!$kelasId)>
                    <option value="">Pilih Pertemuan</option>
                    @for ($i = 1; $i <= 16; $i++)
                        <option value="{{ $i }}">Pertemuan {{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        @if ($tahunAjarId && $kelasId && $pertemuan)
            @if ($students->isNotEmpty())
                <div class="assessment-table-shell mt-4 position-relative">
                    <div class="assessment-loading-layer" wire:loading.flex wire:target="tahunAjarId,kelasId,pertemuan,save">
                        <div class="assessment-loading-box">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Memproses...</span>
                        </div>
                    </div>

                    <div class="assessment-table-head d-flex justify-content-between align-items-center">
                        <div>
                            <div class="assessment-table-title">Lembar Penilaian TPSR</div>
                            <div class="assessment-table-subtitle">
                                Pertemuan {{ $pertemuan }} &bull; Kelas {{ $kelasOptions->firstWhere('id', $kelasId)?->nama }}
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
                                    <th class="text-center" style="width: 10%;">L5</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $siswa)
                                    <tr class="@error('ratings.' . $siswa->id) table-danger-row @enderror" wire:key="student-row-{{ $siswa->id }}-{{ $pertemuan }}">
                                        <td class="align-middle">
                                            <span class="font-weight-bold">{{ $siswa->nama }}</span>
                                            @error('ratings.' . $siswa->id)
                                                <span class="text-danger ml-2 font-weight-bold" style="font-size: 0.8rem;">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                                </span>
                                            @enderror
                                        </td>
                                        @for ($lvl = 0; $lvl <= 5; $lvl++)
                                            <td class="text-center align-middle" wire:key="student-{{ $siswa->id }}-rating-cell-{{ $lvl }}-{{ $pertemuan }}">
                                                <label class="tpsr-radio-container">
                                                    <input type="radio" name="ratings[{{ $siswa->id }}]" value="{{ $lvl }}" wire:model="ratings.{{ $siswa->id }}">
                                                    <span class="tpsr-radio-checkmark"></span>
                                                </label>
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="assessment-table-foot p-3 bg-light d-flex flex-wrap justify-content-end" style="gap: 0.5rem;">
                        <button type="button" class="btn btn-outline-danger px-4 py-2 font-weight-bold" wire:click="kosongkanPenilaian" wire:loading.attr="disabled" wire:target="kosongkanPenilaian">
                            <span wire:loading.remove wire:target="kosongkanPenilaian">
                                <i class="fas fa-eraser mr-2"></i>Kosongkan Penilaian
                            </span>
                            <span wire:loading wire:target="kosongkanPenilaian">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Mengosongkan...
                            </span>
                        </button>

                        <button type="button" class="btn btn-primary px-4 py-2 font-weight-bold" wire:click="save" wire:loading.attr="disabled" wire:target="save">
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
