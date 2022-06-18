<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class SubProjectTable.
 */
class SubProjectTable extends TableComponent
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
    public $parent_id;

    /**
     * @param  string  $parent_id
     */
    public function mount($parentId = 0): void
    {
        $this->parent_id = (int)$parentId;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = Project::with('user');
        $user = auth()->user();
        if ($user->isMasterAdmin()) return $query->where('parent_id', $this->parent_id);

        if ($user->isTeamMember()) {
            $query = Project::join('project_team', 'projects.id', '=', 'project_team.project_id')
                        ->join('teams', 'project_team.team_id', '=', 'teams.id')
                        ->join('team_user', 'teams.id', '=', 'team_user.team_id')
                        ->join('users', 'team_user.user_id', '=', 'users.id')
                        ->select(['projects.*', 'users.name AS username'])
                        ->groupBy('projects.id')
                        ->where('users.id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }
        return $query->where('parent_id', $this->parent_id);
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        $active_columns = auth()->user()->getActiveProjectColumns();
        $columns = [];

        if (in_array('created_at', $active_columns)) {
            $columns[] = Column::make(__('Date'))
                ->view('frontend.user.includes.project.project_date', 'project')
                ->searchable(function ($builder, $term) {
                    return $builder->where('created_at', 'like', '%'.$term.'%');
                })
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('created_at', $direction);
                });
        }
        if (in_array('customer', $active_columns)) {
            $columns[] = Column::make(__('Customer'))
                ->searchable()
                ->sortable();
        }
        if (in_array('projectname', $active_columns)) {
            $columns[] = Column::make(__('Project Name'), 'projectname')
                ->searchable()
                ->sortable();
        }
        if (in_array('fileid', $active_columns)) {
            $columns[] = Column::make(__('UPCs/GTINs'), 'fileid')
                ->searchable()
                ->sortable();
        }
        if (in_array('headline', $active_columns)) {
            $columns[] = Column::make(__('Headline'))
                ->searchable()
                ->sortable();
        }
        if (in_array('size', $active_columns)) {
            $columns[] = Column::make(__('Sizes'), 'size')
                ->searchable()
                ->sortable();
        }
        if (in_array('created_by', $active_columns)) {
            $columns[] = Column::make(__('Created By'), 'created_by')
                ->customAttribute()
                ->html()
                ->searchable(function ($builder, $term) {
                    return $builder->where('created_by', 'like', '%'.$term.'%');
                })
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('created_by', $direction);
                });
        }
        $columns[] = Column::make(__('Approvals'), 'approvals')
                ->view('frontend.user.includes.project.project_approvals', 'project');
        $columns[] = Column::make(__('Actions'))
                ->view('frontend.user.includes.project.project_actions', 'project');

        return $columns;
    }
}
