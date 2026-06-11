<?php

namespace App\Livewire\Siswa;

use App\Models\Siswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class SiswaTable extends DataTableComponent
{
    public string $kelasNama = '';

    public ?int $tahunAjarId = null;

    public string $search = '';

    protected $model = Siswa::class;

    public function configure(): void
    {
        $this->setTableName('siswa-table');
        $this->setPrimaryKey('id');
        $this->setDefaultSort('siswa.id', 'asc');
        $this->setTheme('bootstrap-5');
        $this->setPaginationTheme('bootstrap');
        $this->setPerPageAccepted([10, 25, 50]);
        $this->setSearchDisabled();
        $this->setSortingPillsDisabled();
        $this->setColumnSelectDisabled();
        $this->setExcludeDeselectedColumnsFromQueryDisabled();
        $this->setAdditionalSelects([
            'siswa.id as id',
            'siswa.nama as nama',
            'siswa.gender as gender',
            'siswa.kelas_id as kelas_id',
            'siswa.rata_poin as rata_poin',
        ]);
    }

    public function builder(): Builder
    {
        $sekolahId = Auth::user()?->sekolah?->id;

        return Siswa::query()
            ->with('kelas.tahunAjar')
            ->whereHas('kelas', function (Builder $query) use ($sekolahId) {
                $query->where('sekolah_id', $sekolahId)
                    ->when($this->tahunAjarId, fn (Builder $query) => $query->where('tahun_ajar_id', $this->tahunAjarId))
                    ->when($this->kelasNama !== '', fn (Builder $query) => $query->where('nama', $this->kelasNama));
            })
            ->addSelect([
                'tahun_ajar_nama' => \App\Models\TahunAjar::selectRaw('tahun_ajar.nama')
                    ->join('kelas as k_ta', 'k_ta.tahun_ajar_id', '=', 'tahun_ajar.id')
                    ->whereColumn('k_ta.id', 'siswa.kelas_id')
                    ->limit(1),
            ]);
    }

    public function applySearch(): Builder
    {
        $search = trim($this->search);

        if ($search === '') {
            return $this->getBuilder();
        }

        $this->setBuilder(
            $this->getBuilder()->where(function (Builder $query) use ($search) {
                $query
                    ->where('siswa.nama', 'like', '%'.$search.'%')
                    ->orWhere('siswa.gender', 'like', '%'.$search.'%')
                    ->orWhereHas('kelas', fn (Builder $query) => $query->where('nama', 'like', '%'.$search.'%'))
                    ->orWhereHas('kelas', fn (Builder $query) => $query->whereHas('tahunAjar', fn (Builder $q) => $q->where('nama', 'like', '%'.$search.'%')));
            })
        );

        return $this->getBuilder();
    }

    public function columns(): array
    {
        return [
            Column::make('No')
                ->label(function ($row) {
                    $rows = $this->getRows();
                    $collection = $rows->getCollection();
                    $index = $collection->search(fn ($item) => $item->id === $row->id);

                    if ($index === false) {
                        return '-';
                    }

                    return ($rows->firstItem() ?? 1) + $index;
                }),
            Column::make('Nama', 'nama')
                ->sortable()
                ->searchable(),
            Column::make('Jenis Kelamin', 'gender')
                ->sortable()
                ->searchable(),
            Column::make('Kelas', 'kelas.nama')
                ->sortable()
                ->searchable(),
            Column::make('Tahun Ajar', 'tahun_ajar_nama')
                ->label(fn ($row) => $row->tahun_ajar_nama ?? '-')
                ->sortable(fn (Builder $query, string $direction) => $query->orderBy(
                    \App\Models\TahunAjar::selectRaw('tahun_ajar.nama')
                        ->join('kelas as k_ta', 'k_ta.tahun_ajar_id', '=', 'tahun_ajar.id')
                        ->whereColumn('k_ta.id', 'siswa.kelas_id')
                        ->limit(1),
                    $direction
                )),
            Column::make('Rata-rata Poin', 'rata_poin')
                ->sortable(),
            Column::make('Aksi')
                ->label(fn ($row) => view('siswa.partials.actions', ['siswa' => $row])),
        ];
    }

    #[On('siswa-filter-changed')]
    public function updateKelasFilter(string $kelasNama = '', ?int $tahunAjarId = null): void
    {
        $this->kelasNama = $kelasNama;
        $this->tahunAjarId = $tahunAjarId ?: null;
        $this->resetPage();
    }

    #[On('siswa-search-changed')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = $search;
        $this->resetPage();
    }
}
