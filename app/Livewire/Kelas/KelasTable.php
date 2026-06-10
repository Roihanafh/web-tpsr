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
    public ?int $tahunAjarId = null;

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
            'kelas.tahun_ajar_id as tahun_ajar_id',
        ]);
    }

    public function builder(): Builder
    {
        $sekolahId = Auth::user()?->sekolah?->id;

        return Kelas::query()
            ->with('tahunAjar')
            ->where('sekolah_id', $sekolahId)
            ->when($this->tahunAjarId, fn (Builder $query) => $query->where('tahun_ajar_id', $this->tahunAjarId));
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
                    ->where('kelas.nama', 'like', '%'.$search.'%')
                    ->orWhereHas('tahunAjar', fn (Builder $query) => $query->where('nama', 'like', '%'.$search.'%'));
            })
        );

        return $this->getBuilder();
    }

    public function columns(): array
    {
        return [
            Column::make('No', 'id')->sortable(),
            Column::make('Kelas', 'nama')
                ->sortable()
                ->searchable(),
            Column::make('Tahun Ajaran', 'tahunAjar.nama')
                ->sortable()
                ->searchable(),
            Column::make('Aksi')
                ->label(fn ($row) => view('kelas.partials.actions', ['kelas' => $row])),
        ];
    }

    #[On('kelas-filter-changed')]
    public function updateTahunAjarFilter(?int $tahunAjarId = null): void
    {
        $this->tahunAjarId = $tahunAjarId ?: null;
        $this->resetPage();
    }

    #[On('kelas-search-changed')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = $search;
        $this->resetPage();
    }
}
