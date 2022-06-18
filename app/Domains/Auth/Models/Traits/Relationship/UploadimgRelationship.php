<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\Company;

/**
 * Class UploadimgRelationship.
 */
trait UploadimgRelationship
{
    /**
     * @return mixed
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
