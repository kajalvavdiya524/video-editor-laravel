<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\GridLayoutTemplate;

/**
 * Class GridLayoutRelationship.
 */
trait GridLayoutRelationship
{
    /**
     * @return mixed
     */
    public function templates()
    {
        return $this->hasMany(GridLayoutTemplate::class, 'layout_id');
    }

    public function companies()
    {
        return $this->belongsTomany(Company::class, 'grid_layout_companies');
    }
}
