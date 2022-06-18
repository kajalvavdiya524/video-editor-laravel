<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\PositioningOptionField;
use App\Domains\Auth\Models\Template;

/**
 * Class PositioningOptionRelationship.
 */
trait PositioningOptionRelationship
{
    /**
     * @return mixed
     */

    public function fields() 
    {
        return $this->hasMany(PositioningOptionField::class, 'option_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
}
