<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Job;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class JobService.
 */
class JobService extends BaseService
{
    /**
     * JobService constructor.
     *
     * @param  Job  $job
     */
    public function __construct(Job $job)
    {
        $this->model = $job;
    }

    /**
     * @param  array  $data
     *
     * @return Job
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): Job
    {
        DB::beginTransaction();

        try {
            $job = $this->createJob($data);
        } catch (Exception $e) {
            DB::rollBack();
            throw new GeneralException(__('There was a problem creating this Job. Please try again.'));
        }

        DB::commit();

        return $job;
    }

    /**
     * @param  Job  $job
     * @param  array  $data
     *
     * @return Job
     * @throws \Throwable
     */
    public function update(Job $job, array $data = []): Job
    {

        DB::beginTransaction();
        
        try {
            $job->update([
                'job_statuses_id' => $data['status_id'],
            ]);
            $job->save();
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this Job. Please try again.'));
        }

        
        DB::commit();

        return $job;
    }

    /**
     * @param  Job  $job
     *
     * @return Job
     * @throws GeneralException
     */
    public function delete(Job $job): Job
    {
        if ($this->deleteById($job->id)) {
            return $job;
        }

        throw new GeneralException('There was a problem deleting this Job. Please try again.');
    }

    /**
     * @param Job $job
     *
     * @throws GeneralException
     * @return Job
     */
    public function restore(Job $job): Job
    {
        if ($job->restore()) {
            return $job;
        }

        throw new GeneralException(__('There was a problem restoring job. Please try again.'));
    }

    /**
     * @param  array  $data
     *
     * @return Job
     */
    protected function createJob(array $data = []): Job
    {
        return $this->model::create([
            'key' => $data['key'],
            'company_id' => $data['company'] ?? null,
        ]);
    }


  /**
     * @param  Job  $apiKeys
     *
     * @return Job
     * @throws GeneralException
     */
    public function toggle(Job $apiKeys): Job
    {
        $apiKeys->status = !$apiKeys->status;
        $apiKeys->save();
        return $apiKeys;
    }


}
