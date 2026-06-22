<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class UserTable extends DataTableComponent
{
    public string $search = '';

    protected $model = User::class;

    public function configure(): void
    {
        $this->setTableName('user-table');
        $this->setPrimaryKey('id');
        $this->setDefaultSort('users.id', 'desc');
        $this->setTheme('bootstrap-5');
        $this->setPaginationTheme('bootstrap');
        $this->setPerPageAccepted([10, 25, 50]);
        $this->setSearchDisabled();
        $this->setSortingPillsDisabled();
        $this->setColumnSelectDisabled();
        $this->setExcludeDeselectedColumnsFromQueryDisabled();
        $this->setAdditionalSelects([
            'users.id as id',
            'users.name as name',
            'users.email as email',
            'users.sekolah_id as sekolah_id',
        ]);
    }

    public function builder(): Builder
    {
        return User::query()
            ->with(['roles', 'sekolah'])
            ->when(
                trim($this->search),
                fn ($query) =>
                    $query->where(function (Builder $q) {
                        $q->where('users.name', 'like', '%' . trim($this->search) . '%')
                            ->orWhere('users.email', 'like', '%' . trim($this->search) . '%')
                            ->orWhereHas('sekolah', fn (Builder $q2) => $q2->where('nama', 'like', '%' . trim($this->search) . '%'))
                            ->orWhereHas('roles', fn (Builder $q2) => $q2->where('name', 'like', '%' . trim($this->search) . '%'));
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
            Column::make('Nama', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),
            Column::make('Role')
                ->label(function ($row) {
                    $roles = $row->roles->pluck('name')->toArray();
                    $badges = array_map(function ($r) {
                        $cls = $r === 'admin' ? 'badge-danger' : 'badge-success';
                        return '<span class="badge ' . $cls . '">' . strtoupper($r) . '</span>';
                    }, $roles);
                    return implode(' ', $badges) ?: '-';
                })
                ->html(),
            Column::make('Sekolah', 'sekolah.nama')
                ->label(fn ($row) => $row->sekolah?->nama ?: 'Tidak Ada Sekolah')
                ->sortable()
                ->searchable(),
            Column::make('Aksi')
                ->label(fn ($row) => view('admin.partials.user-actions', ['user' => $row])),
        ];
    }

    #[On('user-search-changed')]
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
