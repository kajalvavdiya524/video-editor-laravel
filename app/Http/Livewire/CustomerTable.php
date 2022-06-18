<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class CustomerTable.
 */
class CustomerTable extends TableComponent
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
        if ($this->status === 'deactivated') {
            return Customer::onlyDeactivated();
        }

        return Customer::onlyActive();
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
                ->view('backend.auth.customer.includes.actions', 'customer'),
        ];
    }
}
