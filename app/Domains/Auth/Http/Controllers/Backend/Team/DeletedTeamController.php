<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Team;

use App\Domains\Auth\Models\Team;
use App\Domains\Auth\Services\TeamService;
use App\Http\Controllers\Controller;

/**
 * Class DeletedTeamController.
 */
class DeletedTeamController extends Controller
{
    /**
     * @var TeamService
     */
    protected $teamService;

    /**
     * DeletedTeamController constructor.
     *
     * @param  TeamService  $teamService
     */
    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('backend.auth.team.deleted');
    }

    /**
     * @param  Team  $deletedTeam
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function update(Team $deletedTeam)
    {
        $this->teamService->restore($deletedTeam);

        return redirect()->route('admin.auth.team.index')->withFlashSuccess(__('The team was successfully restored.'));
    }

    /**
     * @param  Team  $deletedTeam
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(Team $deletedTeam)
    {
        abort_unless(config('boilerplate.access.team.permanently_delete'), 404);

        $this->teamService->destroy($deletedTeam);

        return redirect()->route('admin.auth.team.deleted')->withFlashSuccess(__('The team was permanently deleted.'));
    }
}
