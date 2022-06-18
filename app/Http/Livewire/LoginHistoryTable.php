<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\LoginHistory;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class LoginHistoryTable.
 */
class LoginHistoryTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'login_at';

    /**
     * @var string
     */
    public $sortDirection = 'desc';

    /**
     * @var string
     */
    public $company_id;

    /**
     * @param  string  $company id
     */
    public function mount($companyId = 0): void
    {
        $this->company_id = (int)$companyId;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = LoginHistory::with('user.company');
        $company_id = $this->company_id;
        if ($company_id != 0) {
            $query->whereHas('user', function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            });
        }

        return $query;
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Login At'), 'login_at')
                ->searchable()
                ->sortable(),
            Column::make(__('Name'), 'user.name')
                ->searchable()
                ->sortable(),
            Column::make(__('E-mail'), 'user.email')
                ->searchable()
                ->sortable(),
            Column::make(__('Company'), 'user.company.name')
                ->searchable()
                ->sortable(),
        ];
    }
}
