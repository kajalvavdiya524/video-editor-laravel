<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\PositioningOption;
use App\Domains\Auth\Models\TemplateField;

/**
 * Class TemplateRelationship.
 */
trait TemplateRelationship
{
    /**
     * @return mixed
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function fields()
    {
        return $this->hasMany(TemplateField::class);
    }

    public function positioning_options()
    {
        return $this->hasMany(PositioningOption::class);
    }
}
