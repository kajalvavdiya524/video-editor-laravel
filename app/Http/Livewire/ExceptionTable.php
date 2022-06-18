<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\MyException;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class ExceptionTable.
 */
class ExceptionTable extends TableComponent
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
    public $status;

    /**
     * @param  string  $status
     */
    public function mount($status = 'active', $userId = 0): void
    {
        $this->status = $status;
        $this->user_id = (int)$userId;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = MyException::with('company', 'user');
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
            Column::make(__('Date'), 'uploaded_at')
                ->customAttribute()
                ->html()
                ->searchable(function ($builder, $term) {
                    return $builder->where('updated_at', 'like', '%'.$term.'%');
                })
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('updated_at', $direction);
                }),
            Column::make(__('File ID'), 'file_id')
                ->searchable()
                ->sortable(),
            Column::make(__('Company'), 'company.name')
            ->searchable()
            ->sortable(function ($builder, $direction) {
                return $builder->orderBy('companies.name', $direction);
            }),
            Column::make(__('User'), 'user.name')
                ->searchable()
                ->sortable(),
            Column::make(__('Message'))
                ->searchable()
                ->sortable(),
            Column::make(__('Actions'))
                ->view('backend.auth.settings.advanced.includes.exception_actions', 'exception'),
        ];
    }
}
