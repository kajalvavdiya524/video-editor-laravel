<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\User;

/**
 * Class ExceptionRelationship.
 */
trait ExceptionRelationship
{
    /**
     * @return mixed
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
