<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\ProductSelection;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class ProductSelectionTable.
 */
class ProductSelectionTable extends TableComponent
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
        $query = ProductSelection::with('file');
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
            Column::make(__('Filename'), 'file.name')
                ->searchable()
                ->sortable(),
            Column::make(__('Selection Count'), 'count')
                ->searchable()
                ->sortable(),
            Column::make(__('Actions'))
                ->view('backend.auth.settings.advanced.includes.product_selection_actions', 'product_selection'),
        ];
    }
}
