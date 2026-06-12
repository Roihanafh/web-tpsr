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
        return User::query()->with(['roles', 'sekolah']);
    }

    public function applySearch(): Builder
    {
        $search = trim($this->search);

        if ($search === '') {
            return $this->getBuilder();
        }

        $this->setBuilder(
            $this->getBuilder()->where(function (Builder $query) use ($search) {
                $query->where('users.name', 'like', '%' . $search . '%')
                    ->orWhere('users.email', 'like', '%' . $search . '%')
                    ->orWhereHas('sekolah', fn (Builder $q) => $q->where('nama', 'like', '%' . $search . '%'))
                    ->orWhereHas('roles', fn (Builder $q) => $q->where('name', 'like', '%' . $search . '%'));
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
}
