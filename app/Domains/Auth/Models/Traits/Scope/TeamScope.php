<?php

namespace App\Domains\Auth\Models\Traits\Scope;

/**
 * Class TeamScope.
 */
trait TeamScope
{
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeOnlyDeactivated($query)
    {
        return $query->where($this->getTable().".active", false);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeOnlyActive($query)
    {
        return $query->where($this->getTable().".active", true);
    }
}
