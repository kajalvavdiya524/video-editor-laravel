<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\Company;

/**
 * Class UrlsFileRelationship.
 */
trait UrlsFileRelationship
{
    /**
     * @return mixed
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
