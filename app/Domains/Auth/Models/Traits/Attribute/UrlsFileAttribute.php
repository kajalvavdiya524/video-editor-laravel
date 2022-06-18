<?php

namespace App\Domains\Auth\Models\Traits\Attribute;

/**
 * Trait UrlsFileAttribute.
 */
trait UrlsFileAttribute
{
    /**
     * @return string
     */
    public function getUploadedAtAttribute()
    {
        return $this->created_at;
    }
}
