<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\History;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class HistoryTable.
 */
class HistoryTable extends TableComponent
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
        $query = History::with('user');
        if (! auth()->user()->isMasterAdmin()) {
            $query = History::with('user')->where('user_id', auth()->user()->id);
        }
        return $query;
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        $active_columns = auth()->user()->getActiveDraftColumns();
        $columns = [];

        $columns[] = Column::make(__(''))->view('frontend.user.includes.draft.select');
        foreach ($active_columns as $col) {
            if ($col == 'created_at') {
                $columns[] = Column::make(__('Date'))
                    ->view('frontend.user.includes.draft.history_date', 'history')
                    ->searchable(function ($builder, $term) {
                        return $builder->where('created_at', 'like', '%'.$term.'%');
                    })
                    ->sortable(function ($builder, $direction) {
                        return $builder->orderBy('created_at', $direction);
                    });
            } else if ($col == 'customer') {
                $columns[] = Column::make(__('Customer'))
                    ->searchable()
                    ->sortable();
            } else if ($col == 'projectname') {
                $columns[] = Column::make(__('Project Name'), 'projectname')
                    ->searchable()
                    ->sortable();
            } else if ($col == 'fileid') {
                $columns[] = Column::make(__('UPCs/GTINs'), 'fileid')
                    ->searchable()
                    ->sortable();
            } else if ($col == 'headline') {
            //     $columns[] = Column::make(__('Headline'))
            //         ->searchable()
            //         ->sortable();
            } else if ($col == 'size') {
                $columns[] = Column::make(__('Sizes'), 'size')
                    ->searchable()
                    ->sortable();
            } else if ($col == 'created_by') {
                $columns[] = Column::make(__('Created By'), 'user.name')
                    ->searchable()
                    ->sortable();
            }
        }
        $columns[] = Column::make(__('Actions'))
            ->view('frontend.user.includes.draft.history_actions', 'history');

        return $columns;
    }
}
