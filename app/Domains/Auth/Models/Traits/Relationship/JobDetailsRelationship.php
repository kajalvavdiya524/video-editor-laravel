<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\JobStatus;
use App\Domains\Auth\Models\Job;

/**
 * Class JobRelationship.
 */
trait JobDetailsRelationship
{

     
    public function job_statuses()
    {
        return $this->belongsTo(JobStatus::class);
    }


}





