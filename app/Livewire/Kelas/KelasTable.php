<?php

namespace App\Livewire\Kelas;

use App\Models\Kelas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class KelasTable extends DataTableComponent
{
    public string $search = '';

    protected $model = Kelas::class;

    public function configure(): void
    {
        $this->setTableName('kelas-table');
        $this->setPrimaryKey('id');
        $this->setDefaultSort('kelas.nama', 'asc');
        $this->setTheme('bootstrap-5');
        $this->setPaginationTheme('bootstrap');
        $this->setPerPageAccepted([10, 25, 50]);
        $this->setSearchDisabled();
        $this->setSortingPillsDisabled();
        $this->setColumnSelectDisabled();
        $this->setExcludeDeselectedColumnsFromQueryDisabled();
        $this->setAdditionalSelects([
            'kelas.id as id',
            'kelas.nama as nama',
            'kelas.sekolah_id as sekolah_id',
        ]);
    }

    public function builder(): Builder
    {
        $sekolahId = Auth::user()?->sekolah?->id;
        return Kelas::query()
        ->where('sekolah_id', $sekolahId)
        ->when(
            trim($this->search),
            fn ($query) =>
                $query->where('kelas.nama', 'like', '%' . trim($this->search) . '%')
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
            Column::make('Kelas', 'nama')->sortable()->searchable(),
            Column::make('Aksi')->label(fn ($row) => view('kelas.partials.actions', ['kelas' => $row])),
        ];
    }

    #[On('kelas-search-changed')]
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
