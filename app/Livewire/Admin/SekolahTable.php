<?php

namespace App\Livewire\Admin;

use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class SekolahTable extends DataTableComponent
{
    public string $search = '';

    protected $model = Sekolah::class;

    public function configure(): void
    {
        $this->setTableName('sekolah-table');
        $this->setPrimaryKey('id');
        $this->setDefaultSort('sekolah.id', 'desc');
        $this->setTheme('bootstrap-5');
        $this->setPaginationTheme('bootstrap');
        $this->setPerPageAccepted([10, 25, 50]);
        $this->setSearchDisabled();
        $this->setSortingPillsDisabled();
        $this->setColumnSelectDisabled();
        $this->setExcludeDeselectedColumnsFromQueryDisabled();
        $this->setAdditionalSelects([
            'sekolah.id as id',
            'sekolah.nama as nama',
            'sekolah.alamat as alamat',
        ]);
    }

    public function builder(): Builder
    {
        return Sekolah::query()
            ->when(
                trim($this->search),
                fn ($query) =>
                    $query->where(function (Builder $q) {
                        $q->where('sekolah.nama', 'like', '%' . trim($this->search) . '%')
                          ->orWhere('sekolah.alamat', 'like', '%' . trim($this->search) . '%');
                    })
            );
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
            Column::make('Nama Sekolah', 'nama')
                ->sortable()
                ->searchable(),
            Column::make('Alamat', 'alamat')
                ->label(fn ($row) => $row->alamat ?: '-')
                ->sortable()
                ->searchable(),
            Column::make('Aksi')
                ->label(fn ($row) => view('admin.partials.sekolah-actions', ['sekolah' => $row])),
        ];
    }

    #[On('sekolah-search-changed')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = $search;
        $this->resetPage();
    }

    #[On('refreshDatatable')]
    public function refreshTable(): void
    {
        $this->resetPage();
    }
}
