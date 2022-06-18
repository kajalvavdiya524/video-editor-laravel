<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class ProjectTable.
 */
class ProjectTable extends TableComponent
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
        $query = Project::with('user');
        $user = auth()->user();
        if ($user->isMasterAdmin()) return $query;

        if ($user->isTeamMember()) {
            $query = Project::join('project_team', 'projects.id', '=', 'project_team.project_id')
                        ->join('teams', 'project_team.team_id', '=', 'teams.id')
                        ->join('team_user', 'teams.id', '=', 'team_user.team_id')
                        ->join('users', 'team_user.user_id', '=', 'users.id')
                        ->select(['projects.*', 'users.name AS username'])
                        ->groupBy('projects.id')
                        ->where('users.id', $user->id);
            return $query;
        } else {
            $query->where('user_id', $user->id);
        }
        return $query;
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        $active_columns = auth()->user()->getActiveProjectColumns();
        $columns = [];

        $columns[] = Column::make(__(''))->view('frontend.user.includes.project.select');
        foreach ($active_columns as $col) {
            if ($col == 'created_at') {
                $columns[] = Column::make(__('Date'))
                    ->view('frontend.user.includes.project.project_date', 'project')
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
            } else if ($col == 'approvals') {
                $columns[] = Column::make(__('Approvals'), 'approvals')
                    ->view('frontend.user.includes.project.project_approvals', 'project');
            }
        }
        $columns[] = Column::make(__('Actions'))
            ->view('frontend.user.includes.project.project_actions', 'project');

        return $columns;
    }
}
