<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\GridLayout;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\Team;

/**
 * Class CompanyRelationship.
 */
trait CompanyRelationship
{
    /**
     * @return mixed
     */

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_companies');
    }

    public function grid_layouts()
    {
        return $this->belongsToMany(GridLayout::class, 'grid_layout_companies');
    }
}
