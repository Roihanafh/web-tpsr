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
    public string $search    = '';

    protected $model = Siswa::class;

    public function configure(): void
    {
        $this->setTableName('siswa-table');
        $this->setPrimaryKey('id');
        $this->setDefaultSort('siswa.nama', 'asc');
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
            ->whereHas('kelas', function (Builder $q) use ($sekolahId) {
                $q->where('sekolah_id', $sekolahId)
                  ->when($this->kelasNama !== '' && $this->kelasNama !== '0',
                      fn (Builder $q2) => $q2->where('nama', $this->kelasNama));
            })
            ->when(
                trim($this->search),
                fn ($query) =>
                    $query->where(function (Builder $q) {
                        $q->where('siswa.nama', 'like', '%' . trim($this->search) . '%')
                          ->orWhereHas('kelas', fn (Builder $q2) => $q2->where('nama', 'like', '%' . trim($this->search) . '%'));
                    })
            );
    }

    public function columns(): array
    {
        return [
            Column::make('No')
                ->label(function ($row) {
                    $rows  = $this->getRows();
                    $index = $rows->getCollection()->search(fn ($item) => $item->id === $row->id);
                    return $index === false ? '-' : ($rows->firstItem() ?? 1) + $index;
                }),
            Column::make('Nama', 'nama')->sortable()->searchable(),
            Column::make('Kelas', 'kelas.nama')->sortable()->searchable(),
            Column::make('Rata-rata Poin', 'rata_poin')->sortable(),
            Column::make('Aksi')->label(fn ($row) => view('siswa.partials.actions', ['siswa' => $row])),
        ];
    }

    #[On('siswa-filter-changed')]
    public function updateFilter(string $kelasNama = ''): void
    {
        $this->kelasNama = $kelasNama;
        $this->resetPage();
    }

    #[On('siswa-search-changed')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = $search;
        $this->resetPage();
    }

    #[On('siswa-deleted')]
    public function onSiswaDeleted(): void { $this->resetPage(); }

    #[On('refreshDatatable')]
    public function refreshTable(): void
    {
        $this->resetPage();
    }
}
