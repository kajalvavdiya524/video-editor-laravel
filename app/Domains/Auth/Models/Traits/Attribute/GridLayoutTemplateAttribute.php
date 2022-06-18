<?php

namespace App\Domains\Auth\Models\Traits\Attribute;

use Illuminate\Support\Facades\Hash;

/**
 * Trait GridLayoutTemplateAttribute.
 */
trait GridLayoutTemplateAttribute
{
    /**
     * @return mixed
     */
    public function getTemplateAttribute()
    {
        return $this->template();
    }
}
