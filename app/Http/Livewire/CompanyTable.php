<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class CompanyTable.
 */
class CompanyTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'name';

    /**
     * @var string
     */
    public $status;

    /**
     * @param  string  $status
     */
    public function mount($status = 'active'): void
    {
        $this->status = $status;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = Company::withCount('users');

        if ($this->status === 'deleted') {
            return $query->onlyTrashed();
        }

        if ($this->status === 'deactivated') {
            return $query->onlyDeactivated();
        }
        return $query->onlyActive();
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Id')),
            Column::make(__('Name'))
                ->searchable()
                ->sortable(),
            Column::make(__('Address'))
                ->searchable()
                ->sortable(),
            Column::make(__('Number of Users'), 'users_count')
                ->sortable(),
            Column::make(__('MRHI'), 'has_mrhi')
                ->sortable(),
            Column::make(__('Active'))
                ->view('backend.auth.company.includes.status', 'company')
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('active', $direction);
                }),
            Column::make(__('Actions'))
                ->view('backend.auth.company.includes.actions', 'company'),
        ];
    }
}
