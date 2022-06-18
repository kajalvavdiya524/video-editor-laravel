<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\ApiKeys;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class ApiKeysTable.
 */
class ApiKeysTable extends TableComponent
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
        $query = ApiKeys::with('company');
        return $query;
        
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Id')),
    
            Column::make(__('Company'), 'company.name')
            ->view('backend.auth.apikeys.includes.company_name', 'apikey')
                ->searchable()
                ->sortable(function ($builder, $direction) {
                    return $builder->select('api_keys.id','company_id','companies.name as company.name', 'api_keys.key','api_keys.status')->orderBy('companies.name', $direction)->orderBy('api_keys.id', $direction);
                }),

            Column::make(__('Key'))->searchable(),
            Column::make(__('Active'))
            ->view('backend.auth.apikeys.includes.status', 'apikey')
            ->sortable(function ($builder, $direction) {
                return $builder->orderBy('status', $direction);
            }),
            Column::make(__('Actions'))
                ->view('backend.auth.apikeys.includes.actions', 'apiKey'),
        ];
    }
}
