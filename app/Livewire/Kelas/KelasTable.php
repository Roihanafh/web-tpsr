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

    protected $model = Kelas::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('kelas.id', 'desc');
        $this->setPerPageAccepted([10, 25, 50]);
        $this->setSearchPlaceholder('Search');
        $this->setSearchDebounce(300);
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
        if (! $this->searchIsEnabled() || ! $this->hasSearch()) {
            return $this->getBuilder();
        }

        $search = $this->getSearch();

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
        $this->tahunAjarId = $tahunAjarId;
        $this->resetPage();
    }
}
