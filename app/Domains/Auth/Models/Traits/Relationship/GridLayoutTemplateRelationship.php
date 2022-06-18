<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\Template;

/**
 * Class GridLayoutTemplateRelationship.
 */
trait GridLayoutTemplateRelationship
{
    /**
     * @return mixed
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
