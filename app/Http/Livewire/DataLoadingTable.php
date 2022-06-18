<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\LoadingHistory;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class DataLoadingTable.
 */
class DataLoadingTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'updated_at';

    /**
     * @var string
     */
    public $sortDirection = 'desc';
    
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = LoadingHistory::with('company', 'user');
        if (auth()->user()->company_id != 0) {
            $query->where('loading_histories.company_id', auth()->user()->company_id);
        }
        return $query;
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Date'), 'uploaded_at')
                ->customAttribute()
                ->html()
                ->searchable(function ($builder, $term) {
                    return $builder->where('updated_at', 'like', '%'.$term.'%');
                })
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('updated_at', $direction);
                }),
            Column::make(__('Company'), 'company.name')
                ->searchable()
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('companies.name', $direction);
                }),
            Column::make(__('User'), 'user.name')
                ->searchable()
                ->sortable(),
            Column::make(__('File'), 'filename')
                ->searchable()
                ->sortable(),
            Column::make(__('Type'))
                ->searchable()
                ->sortable(),
            Column::make(__('Actions'))
                ->view('backend.auth.settings.updatefile.includes.updatefile_actions', 'loading_history'),
        ];
    }
}
