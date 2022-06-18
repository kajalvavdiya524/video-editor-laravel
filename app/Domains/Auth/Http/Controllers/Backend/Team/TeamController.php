<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Team;

use App\Domains\Auth\Http\Requests\Backend\Team\DeleteTeamRequest;
use App\Domains\Auth\Http\Requests\Backend\Team\EditTeamRequest;
use App\Domains\Auth\Http\Requests\Backend\Team\StoreTeamRequest;
use App\Domains\Auth\Http\Requests\Backend\Team\UpdateTeamRequest;
use App\Domains\Auth\Models\Team;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Services\TeamService;
use App\Http\Controllers\Controller;

/**
 * Class TeamController.
 */
class TeamController extends Controller
{
    /**
     * @var TeamService
     */
    protected $teamService;

    /**
     * TeamController constructor.
     *
     * @param  TeamService  $teamService
     */
    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.auth.team.index');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $users = User::where('company_id', auth()->user()->company_id);
        $customers = Customer::all();
        $companies = Company::all();
        return view('backend.auth.team.create')
            ->with('users', $users->get())
            ->with('customers', $customers)
            ->with('companies', $companies);
    }

    /**
     * @param  StoreTeamRequest  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function store(StoreTeamRequest $request)
    {
        $userIds = [];
        $customerIds = [];

        $params = $request->post();
        foreach ($params as $key => $value) {
            if (strpos($key, "member") !== false) {
                $userIds[] = $value;
            }
            if (strpos($key, "customer") !== false) {
                $customerIds[] = $value;
            }
        }
        
        $team = $this->teamService->store($request->validated());
        $team->users()->sync($userIds);
        $team->customers()->sync($customerIds);

        return redirect()->route('admin.auth.team.show', $team)->withFlashSuccess(__('The team was successfully created.'));
    }

    /**
     * @param  Team  $team
     *
     * @return mixed
     */
    public function show(Team $team)
    {
        return view('backend.auth.team.show')
            ->withTeam($team);
    }

    /**
     * @param  EditTeamRequest  $request
     * @param  Team  $team
     *
     * @return mixed
     */
    public function edit(EditTeamRequest $request, Team $team)
    {
        $users = User::where('company_id', $team->company_id);
        $customers = Customer::all();
        $companies = Company::all();
        return view('backend.auth.team.edit')
            ->withTeam($team)
            ->with('companies', $companies)
            ->with('users', $users->get())
            ->with('members', $team->users)
            ->with('customers', $customers)
            ->with('team_customers', $team->customers);
    }

    /**
     * @param  UpdateTeamRequest  $request
     * @param  Team  $team
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $userIds = [];
        $customerIds = [];
        $params = $request->post();
        foreach ($params as $key => $value) {
            if (strpos($key, "member") !== false) {
                $userIds[] = $value;
            }
            if (strpos($key, "customer") !== false) {
                $customerIds[] = $value;
            }
        }
        
        if (! auth()->user()->isMasterAdmin()) {
            $team->users()->sync($userIds);
        }

        $team->customers()->sync($customerIds);

        $this->teamService->update($team, $request->validated());
     
        return redirect()->route('admin.auth.team.show', $team)->withFlashSuccess(__('The team was successfully updated.'));
    }

    /**
     * @param  DeleteTeamRequest  $request
     * @param  Team  $team
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(DeleteTeamRequest $request, Team $team)
    {
        $team->users()->sync(array());
        $team->projects()->sync(array());
        $team->customers()->sync(array());
        $this->teamService->delete($team);
        return redirect()->route('admin.auth.team.index')->withFlashSuccess(__('The team was successfully deleted.'));
    } 
}
