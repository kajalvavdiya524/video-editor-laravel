<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Template;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class TemplateTable.
 */
class TemplateTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'order';

    /**
     * @var string
     */
    public $sortDirection = 'asc';

    /**
     * @var string
     */
    public $customer_id;

    /**
     * @param  string  $customer_id
     */
    public function mount($customerId = 0): void
    {
        $this->customer_id = (int)$customerId;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = Template::with('company')
                        ->where('system', false)
                        ->where('customer_id', $this->customer_id);
        if (!auth()->user()->isMasterAdmin()) {
            $query->whereIn('company_id', [0, auth()->user()->company_id]);
        }

        return $query;
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('ID')),
            Column::make(__('Name'))
                ->searchable()
                ->sortable(),
            Column::make(__('Company'), 'company.name')
                ->searchable()
                ->sortable(),
            Column::make(__('Active'))
                ->view('backend.auth.template.includes.status', 'template')
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('status', $direction);
                }),
            Column::make(__('Actions'))
                ->view('backend.auth.template.includes.actions', 'template'),
        ];
    }
}
