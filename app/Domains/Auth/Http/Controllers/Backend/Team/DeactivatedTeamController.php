<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Team;

use App\Domains\Auth\Models\Team;
use App\Domains\Auth\Services\TeamService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class TeamStatusController.
 */
class DeactivatedTeamController extends Controller
{
    /**
     * @var TeamService
     */
    protected $teamService;

    /**
     * DeactivatedTeamController constructor.
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
        return view('backend.auth.team.deactivated');
    }

    /**
     * @param  Request  $request
     * @param  Team  $team
     * @param $status
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function update(Request $request, Team $team, $status)
    {
        $this->teamService->mark($team, (int) $status);

        return redirect()->route(
            (int) $status === 1 || ! $request->user()->can('access.team.reactivate') ?
                'admin.auth.team.index' :
                'admin.auth.team.deactivated'
        )->withFlashSuccess(__('The team was successfully updated.'));
    }
}
