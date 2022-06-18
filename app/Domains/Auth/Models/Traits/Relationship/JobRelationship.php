<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\ApiKeys;
use App\Domains\Auth\Models\JobDetails;
use App\Domains\Auth\Models\JobStatus;
use App\Domains\Auth\Models\JobTypes;
use App\Domains\Auth\Models\Template;


/**
 * Class JobRelationship.
 */
trait JobRelationship
{

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function job_types()
    {
        return $this->belongsTo(JobTypes::class);
    }

    public function api_keys()
    {
        return $this->belongsTo(ApiKeys::class);
    }
    
    public function job_statuses()
    {
        return $this->belongsTo(JobStatus::class);
    }

    public function details()
    {
        return $this->hasMany(JobDetails::class);
    }

}





