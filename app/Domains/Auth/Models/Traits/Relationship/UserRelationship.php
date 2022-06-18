<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\PasswordHistory;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\History;
use App\Domains\Auth\Models\LoginHistory;
use App\Domains\Auth\Models\ProjectColumn;
use App\Domains\Auth\Models\DraftColumn;
use App\Domains\Auth\Models\Team;

/**
 * Class UserRelationship.
 */
trait UserRelationship
{
    /**
     * @return mixed
     */
    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function projectColumn()
    {
        return $this->hasOne(ProjectColumn::class);
    }

    public function draftColumn()
    {
        return $this->hasOne(DraftColumn::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function teams() 
    {
        return $this->belongsToMany(Team::class, 'team_user');
    }
}
