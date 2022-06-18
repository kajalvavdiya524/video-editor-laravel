<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\GridLayout;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class GridLayoutTable.
 */
class GridLayoutTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'created_at';

    /**
     * @var string
     */
    public $sortDirection = 'desc';

    /**
     * @var string
     */
    public $user_id;

    /**
     * @var string
     */
    public $company_id;

    /**
     * @param  string  $status
     */
    public function mount($userId, $companyId, $customerId): void
    {
        $this->user_id = (int)$userId;
        $this->company_id = (int)$companyId;
        $this->customer_id = (int)$customerId;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        if (auth()->user()->isMasterAdmin()) {
            return GridLayout::join('grid_layout_companies', 'grid_layouts.id', '=', 'grid_layout_companies.grid_layout_id')
                        ->select(['grid_layouts.*'])
                        ->where('grid_layouts.customer_id', $this->customer_id);
                        // master admin should be able to se everything
                       // ->where('grid_layout_companies.company_id', $this->company_id);
        } else {
            return GridLayout::join('grid_layout_companies', 'grid_layouts.id', '=', 'grid_layout_companies.grid_layout_id')
                        ->join('companies', 'grid_layout_companies.company_id', '=', 'companies.id')
                        ->select(['grid_layouts.*'])
                        ->where('grid_layouts.customer_id', $this->customer_id)
                        ->where('companies.id', $this->company_id);
        }
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
            Column::make(__('Actions'))
                ->view('frontend.group.includes.actions', 'layout'),
        ];
    }
}
