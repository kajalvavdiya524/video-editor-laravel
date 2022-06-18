<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Job;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Auth\Services\JobService;
use App\Domains\Auth\Models\Job;
use App\Domains\Auth\Models\JobDetails;
use App\Domains\Auth\Models\JobStatus;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\GeneralException;

class JobController extends Controller
{


        /**
     * JobController constructor.
     *
     * @param  JobService  $jobService
     */
    public function __construct(JobService $jobService)
    {
        $this->jobService = $jobService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.auth.job.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Job $job)
    {
        return view('backend.auth.job.show')
        ->withJob($job);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, job $job)
    {
        $statuses = JobStatus::all();
        return view('backend.auth.job.edit')
            ->withJob($job)
            ->with('statuses', $statuses);
            
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Job $job)
    {
        $validator = Validator::make($request->all(), [
            "status_id" => "required|integer|exists:job_statuses,id",
        ]);

        if ($validator->fails()) {
            throw new GeneralException(__('Invalid Job status'));
        }

        $data = $validator->validated();

        $this->jobService->update($job, $data);
        return redirect()->route('admin.auth.job.index')->withFlashSuccess(__('The Job was successfully updated.'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Job $job)
    {
        JobDetails::where("job_id",$job->id)->delete();
        $this->jobService->delete($job);
        return redirect()->route('admin.auth.job.index')->withFlashSuccess(__('The job was successfully deleted.'));
    }
}
