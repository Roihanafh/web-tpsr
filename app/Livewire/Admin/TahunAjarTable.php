<?php

namespace App\Livewire\Admin;

use App\Models\TahunAjar;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class TahunAjarTable extends DataTableComponent
{
    public string $search = '';

    protected $model = TahunAjar::class;

    public function configure(): void
    {
        $this->setTableName('tahun-ajar-table');
        $this->setPrimaryKey('id');
        $this->setDefaultSort('tahun_ajar.id', 'desc');
        $this->setTheme('bootstrap-5');
        $this->setPaginationTheme('bootstrap');
        $this->setPerPageAccepted([10, 25, 50]);
        $this->setSearchDisabled();
        $this->setSortingPillsDisabled();
        $this->setColumnSelectDisabled();
        $this->setExcludeDeselectedColumnsFromQueryDisabled();
        $this->setAdditionalSelects([
            'tahun_ajar.id as id',
            'tahun_ajar.nama as nama',
        ]);
    }

    public function builder(): Builder
    {
        return TahunAjar::query();
    }

    public function applySearch(): Builder
    {
        $search = trim($this->search);

        if ($search === '') {
            return $this->getBuilder();
        }

        $this->setBuilder(
            $this->getBuilder()->where('tahun_ajar.nama', 'like', '%' . $search . '%')
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
            Column::make('Tahun Ajaran / Semester', 'nama')
                ->sortable()
                ->searchable(),
            Column::make('Aksi')
                ->label(fn ($row) => view('admin.partials.tahun-ajar-actions', ['tahunAjar' => $row])),
        ];
    }

    #[On('tahun-ajar-search-changed')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = $search;
        $this->resetPage();
    }
}
