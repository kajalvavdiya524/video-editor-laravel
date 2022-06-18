<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\ProjectApproval;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\Team;

/**
 * Class ProjectRelationship.
 */
trait ProjectRelationship
{
    /**
     * @return mixed
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function teams() 
    {
        return $this->belongsToMany(Team::class, 'project_team');
    }

    public function approvals()
    {
        return $this->hasMany(ProjectApproval::class, 'project_id');
    }
}
