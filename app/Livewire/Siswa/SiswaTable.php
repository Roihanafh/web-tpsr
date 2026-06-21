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
    public string  $kelasNama  = '';
    public ?string $isGanjil   = null;  // '1', '0', atau null
    public string  $search     = '';

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
            'siswa.kelas_id as kelas_id',
            'siswa.rata_poin as rata_poin',
        ]);
    }

    public function builder(): Builder
    {
        $sekolahId = Auth::user()?->sekolah?->id;

        return Siswa::query()
            ->with('kelas')
            ->whereHas('kelas', function (Builder $query) use ($sekolahId) {
                $query->where('sekolah_id', $sekolahId)
                    ->when($this->isGanjil !== null, fn (Builder $q) => $q->where('is_ganjil', (bool) $this->isGanjil))
                    ->when($this->kelasNama !== '' && $this->kelasNama !== '0', fn (Builder $q) => $q->where('nama', $this->kelasNama));
            });
    }

    public function applySearch(): Builder
    {
        $search = trim($this->search);

        if ($search === '') {
            return $this->getBuilder();
        }

        $this->setBuilder(
            $this->getBuilder()->where(function (Builder $query) use ($search) {
                $query->where('siswa.nama', 'like', '%' . $search . '%')
                    ->orWhereHas('kelas', fn (Builder $q) => $q->where('nama', 'like', '%' . $search . '%'));
            })
        );

        return $this->getBuilder();
    }

    public function columns(): array
    {
        return [
            Column::make('No')
                ->label(function ($row) {
                    $rows       = $this->getRows();
                    $collection = $rows->getCollection();
                    $index      = $collection->search(fn ($item) => $item->id === $row->id);

                    return $index === false ? '-' : ($rows->firstItem() ?? 1) + $index;
                }),
            Column::make('Nama', 'nama')
                ->sortable()
                ->searchable(),
            Column::make('Kelas', 'kelas.nama')
                ->sortable()
                ->searchable(),
            Column::make('Semester')
                ->label(fn ($row) => $row->kelas?->is_ganjil ? 'Ganjil' : 'Genap'),
            Column::make('Rata-rata Poin', 'rata_poin')
                ->sortable(),
            Column::make('Aksi')
                ->label(fn ($row) => view('siswa.partials.actions', ['siswa' => $row])),
        ];
    }

    #[On('siswa-filter-changed')]
    public function updateFilter(string $kelasNama = '', mixed $isGanjil = null): void
    {
        $this->kelasNama = $kelasNama;
        $this->isGanjil  = $isGanjil !== null ? (string) $isGanjil : null;
        $this->resetPage();
    }

    #[On('siswa-search-changed')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = $search;
        $this->resetPage();
    }

    #[On('siswa-deleted')]
    public function onSiswaDeleted(): void
    {
        $this->resetPage();
    }
}
