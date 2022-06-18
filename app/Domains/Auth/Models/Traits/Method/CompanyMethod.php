<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait CompanyMethod.
 */
trait CompanyMethod
{
    
    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

}
