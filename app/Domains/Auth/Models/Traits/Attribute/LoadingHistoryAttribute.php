<?php

namespace App\Domains\Auth\Models\Traits\Attribute;

/**
 * Trait LoadingHistoryAttribute.
 */
trait LoadingHistoryAttribute
{
    /**
     * @return string
     */
    public function getUploadedAtAttribute()
    {
        return $this->updated_at;
    }
}
