<?php

namespace App\Domains\Auth\Models\Traits\Scope;

/**
 * Class TemplateScope.
 */
trait TemplateScope
{
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeOnlyActive($query)
    {
        return $query->where($this->getTable().".status", true)->orderBy($this->getTable().".order");
    }
}
