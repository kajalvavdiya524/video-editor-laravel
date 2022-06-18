<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class TeamTable.
 */
class TeamTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'id';

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
        $query = Team::with('company');
        if (! auth()->user()->isMasterAdmin()) {
            $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
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
            Column::make(__('Company'), 'company.name')
                ->searchable()
                ->sortable(),
            Column::make(__('Actions'))
                ->view('backend.auth.team.includes.actions', 'team'),
        ];
    }
}
