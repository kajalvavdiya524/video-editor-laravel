<?php

namespace App\Domains\Auth\Models\Traits\Attribute;
use App\Domains\Auth\Models\User;

/**
 * Trait ProjectAttribute.
 */
trait ProjectAttribute
{
    /**
     * @return string
     */
    public function getCreatedByAttribute(): string
    {
        $user = User::where('id', $this->user_id)->first();
        return isset($user->name) ? $user->name : '';
    }

}
