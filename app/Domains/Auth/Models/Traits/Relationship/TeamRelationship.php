<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\Project;

/**
 * Class TeamRelationship.
 */
trait TeamRelationship
{
    /**
     * @return mixed
     */

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customers() 
    {
        return $this->belongsToMany(Customer::class, 'team_customer');
    }
    
    public function projects() 
    {
        return $this->belongsToMany(Project::class, 'project_team');
    }
}
