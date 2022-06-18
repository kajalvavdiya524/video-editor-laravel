<?php

namespace App\Domains\Auth\Models\Traits\Attribute;

/**
 * Trait UploadimgAttribute.
 */
trait UploadimgAttribute
{
    /**
     * @return string
     */
    public function getUploadedAtAttribute()
    {
        return $this->updated_at;
    }
}
