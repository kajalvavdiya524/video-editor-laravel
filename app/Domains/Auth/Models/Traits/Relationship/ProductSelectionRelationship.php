<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\File;
use App\Domains\Auth\Models\Company;

/**
 * Class ProductSelectionRelationship.
 */
trait ProductSelectionRelationship
{
    /**
     * @return mixed
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

}
