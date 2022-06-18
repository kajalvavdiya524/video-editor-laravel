<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Team;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\User;

/**
 * Class CustomerRelationship.
 */
trait CustomerRelationship
{
    /**
     * @return mixed
     */

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_customer');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'customer_companies');
    }

    public function templates()
    {
        return $this->hasMany(Template::class)->onlyActive();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
