<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\Company;


/**
 * Class ApiKeysRelationship.
 */
trait ApiKeysRelationship
{

    public function company()
    {
        return $this->belongsTo(Company::class);
 
    }
}
