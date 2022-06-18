<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Team;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class TeamService.
 */
class TeamService extends BaseService
{
    /**
     * TeamService constructor.
     *
     * @param  Team  $team
     */
    public function __construct(Team $team)
    {
        $this->model = $team;
    }

    /**
     * @param  array  $data
     *
     * @return Team
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): Team
    {
        DB::beginTransaction();

        try {
            $team = $this->createTeam($data);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem creating this team. Please try again.'));
        }

        DB::commit();

        return $team;
    }

    /**
     * @param  Team  $team
     * @param  array  $data
     *
     * @return Team
     * @throws \Throwable
     */
    public function update(Team $team, array $data = []): Team
    {
        DB::beginTransaction();

        try {
            $team->update([
                'name' => $data['name'],
                'company_id' => $data['company'],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this team. Please try again.'));
        }

        DB::commit();

        return $team;
    }

    /**
     * @param  Team  $team
     *
     * @return Team
     * @throws GeneralException
     */
    public function delete(Team $team): Team
    {
        if ($this->deleteById($team->id)) {
            return $team;
        }

        throw new GeneralException('There was a problem deleting this team. Please try again.');
    }

    /**
     * @param Team $team
     *
     * @throws GeneralException
     * @return Team
     */
    public function restore(Team $team): Team
    {
        if ($team->restore()) {
            return $team;
        }

        throw new GeneralException(__('There was a problem restoring this team. Please try again.'));
    }

    /**
     * @param  array  $data
     *
     * @return Team
     */
    protected function createTeam(array $data = []): Team
    {
        return $this->model::create([
            'name' => $data['name'] ?? null,
            'company_id' => $data['company'] ?? null,
        ]);
    }
}
