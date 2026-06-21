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
    public ?string $isGanjilFilter = null;  // '1', '0', atau null = semua

    public string $search = '';

    protected $model = Kelas::class;

    public function configure(): void
    {
        $this->setTableName('kelas-table');
        $this->setPrimaryKey('id');
        $this->setDefaultSort('kelas.id', 'desc');
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
            'kelas.is_ganjil as is_ganjil',
        ]);
    }

    public function builder(): Builder
    {
        $sekolahId = Auth::user()?->sekolah?->id;

        return Kelas::query()
            ->where('sekolah_id', $sekolahId)
            ->when($this->isGanjilFilter !== null, function (Builder $query) {
                $query->where('is_ganjil', (bool) $this->isGanjilFilter);
            });
    }

    public function applySearch(): Builder
    {
        $search = trim($this->search);

        if ($search === '') {
            return $this->getBuilder();
        }

        $this->setBuilder(
            $this->getBuilder()->where('kelas.nama', 'like', '%' . $search . '%')
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
            Column::make('Kelas', 'nama')
                ->sortable()
                ->searchable(),
            Column::make('Semester')
                ->label(fn ($row) => $row->is_ganjil ? 'Ganjil' : 'Genap'),
            Column::make('Aksi')
                ->label(fn ($row) => view('kelas.partials.actions', ['kelas' => $row])),
        ];
    }

    #[On('kelas-filter-changed')]
    public function updateIsGanjilFilter(mixed $isGanjil = null): void
    {
        $this->isGanjilFilter = $isGanjil !== null ? (string) $isGanjil : null;
        $this->resetPage();
    }

    #[On('kelas-search-changed')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = $search;
        $this->resetPage();
    }
}
