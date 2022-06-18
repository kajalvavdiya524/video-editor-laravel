<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Job;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class JobsTable.
 */
class JobsTable extends TableComponent
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
        $query = Job::with(array('template','job_types','api_keys','job_statuses'));
        return $query;
        
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Id')),
            Column::make(__('Date'), 'created_at')
            ->customAttribute()->sortable(),
            Column::make(__('Template'), 'template.name')
            ->searchable()
            ->sortable(),
            Column::make(__('Requested By'), 'api_keys.id')
            ->view('backend.auth.job.includes.company_name', 'api_key')
            ->searchable()
            ->sortable(),

            Column::make(__('Type'), 'job_types.name')
                ->searchable()
                ->sortable(),    
            
            Column::make(__('Status'), 'job_statuses.name')
                ->searchable()
                ->sortable(),    
            
                Column::make(__('Actions'))
                ->view('backend.auth.job.includes.actions', 'job'),
        ];
    }
}
