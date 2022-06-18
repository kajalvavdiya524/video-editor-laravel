<?php

namespace App\Domains\Auth\Models\Traits\Attribute;

/**
 * Trait ExceptionAttribute.
 */
trait ExceptionAttribute
{
    /**
     * @return string
     */
    public function getUploadedAtAttribute()
    {
        return $this->updated_at;
    }
}
