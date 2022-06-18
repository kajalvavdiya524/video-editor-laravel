<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\VideoProject;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class VideoDraftTable.
 */
class VideoDraftTable extends TableComponent
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
    public function mount($userId = 0): void
    {
        $this->user_id = (int)$userId;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = VideoProject::with('user')->where('is_draft', true);
        if (! auth()->user()->isMasterAdmin()) {
            $query = VideoProject::with('user')->where('user_id', auth()->user()->id);
            $query = VideoProject::with('user')->Where('user_id', auth()->user()->id)
                                ->where('visibility', true)
                                ->where('is_draft', true);
        }
        return $query;
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        $active_columns = auth()->user()->getVideoDraftColumns();
        $columns = [];

        foreach ($active_columns as $col) {
            if ($col == 'created_at') {
                $columns[] = Column::make(__('Date'))
                    ->view('frontend.user.includes.video_project.project_date', 'project')
                    ->searchable(function ($builder, $term) {
                        return $builder->where('created_at', 'like', '%'.$term.'%');
                    })
                    ->sortable(function ($builder, $direction) {
                        return $builder->orderBy('created_at', $direction);
                    });
            } else if ($col == 'projectname') {
                $columns[] = Column::make(__('Project Name'), 'name')
                    ->searchable()
                    ->sortable();
            } else if ($col == 'size') {
                $columns[] = Column::make(__('Sizes'))
                    ->view('frontend.user.includes.video_project.project_size', 'project')
                    ->searchable(function ($builder, $term) {
                        return $builder->where(function ($query) use ($term) {
                            $query->where('screen_width', 'LIKE', "%$term%")
                                ->orWhere('screen_height', 'LIKE', "%$term%");
                        });
                    });
            } else if ($col == 'created_by') {
                $columns[] = Column::make(__('Created By'), 'user.name');
            }
        }
        $columns[] = Column::make(__('Actions'))
            ->view('frontend.user.includes.video_project.project_actions', 'project');

        return $columns;
    }
}
